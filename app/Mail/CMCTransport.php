<?php

namespace App\Mail;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Swift_Mime_SimpleMessage;
use Swift_Events_EventDispatcher;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Facades\Log;

/**
 * Class Transport
 *
 * @package CustomersMailCloud
 */
class CMCTransport extends Transport
{

    /**
     * Guzzle client instance.
     *
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * The Customers Mail Cloud API key.
     *
     * @var string
     */
    protected string $key;

    /**
     * The Customers Mail Cloud API User.
     *
     * @var string
     */
    protected string $apiUser;

    /**
     * The Customers Mail Cloud API endpoint.
     *
     * @var string
     */
    protected string $endpoint;

    /**
     * A set of default headers to attach to every message
     *
     * @var array
     */
    protected array $defaultHeaders = [];

    protected Swift_Events_EventDispatcher $eventDispatcher;


    /**
     * Create a new CustomersMailCloud transport instance.
     *
     * @param string $key
     * @param string $apiUser
     * @param string $endpoint
     * @param array  $defaultHeaders
     *
     * @return void
     */
    public function __construct(string $key, string $apiUser, string $endpoint, array $defaultHeaders=[])
    {
        $this->key = $key;
        $this->apiUser = $apiUser;
        $this->endpoint = $endpoint;
        $this->defaultHeaders = $defaultHeaders;
        $this->eventDispatcher = \Swift_DependencyContainer::getInstance()->lookup('transport.eventdispatcher');

    }//end __construct()


    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * This is the responsibility of the send method to start the transport if needed.
     *
     * @param $message $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients=null)
    {
        $client = $this->getHttpClient();
        $sendEvent = $this->eventDispatcher->createSendEvent($this, $message);
        $this->eventDispatcher->dispatchEvent($sendEvent, 'beforeSendPerformed');
        if ($sendEvent->bubbleCancelled()) {
            return 0;
        }

        $response = $client->request(
            'POST',
            $this->endpoint,
            [
                'headers'     => [
                    'Content-Type'    => 'application/json',
                    'Accept-Language' => 'en',
                ],
                'json'        => $this->getMessagePayload($message),
                'http_errors' => false,
            ]
        );
        $success = $response->getStatusCode() === 200;
        if (!$success) {
            Log::error($response->getBody()->getContents());
        }

        $responseEvent = $this->eventDispatcher->createResponseEvent(
            $this,
            $response->getBody()->__toString(),
            $success
        );
        $this->eventDispatcher->dispatchEvent($responseEvent, 'responseReceived');

        $sendEvent->setResult(
            $success ? \Swift_Events_SendEvent::RESULT_SUCCESS : \Swift_Events_SendEvent::RESULT_FAILED
        );
        $this->eventDispatcher->dispatchEvent($sendEvent, 'sendPerformed');

        return $success ? $this->numberOfRecipients($message) : 0;

    }//end send()


    /**
     * Get the number of recipients.
     *
     * @param \Swift_Mime_SimpleMessage $message
     */
    protected function numberOfRecipients(Swift_Mime_SimpleMessage $message)
    {
        return count(
            array_merge((array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc())
        );

    }//end numberOfRecipients()


    /**
     * Convert email dictionary with emails and names
     * to array of emails with names.
     *
     * @param array $emails
     *
     * @return array
     */
    protected function convertEmailsArray(array $emails)
    {
        $convertedEmails = [];
        foreach ($emails as $email => $name) {
            if (empty($name)) {
                $convertedEmails[] = [
                    'name'    => '',
                    'address' => $email,
                ];
            } else {
                $convertedEmails[] = [
                    'name'    => $name,
                    'address' => $email,
                ];
            }
        }

        return $convertedEmails;

    }//end convertEmailsArray()


    /**
     * Gets MIME parts that match the message type.
     * Exclude parts of type \Swift_Mime_Attachment as those
     * are handled later.
     *
     * @param Swift_Mime_SimpleMessage $message
     * @param string                   $mimeType
     *
     * @return \Swift_Mime_SimpleMimeEntity|void
     */
    protected function getMIMEPart(Swift_Mime_SimpleMessage $message, string $mimeType)
    {
        foreach ($message->getChildren() as $part) {
            if (strpos($part->getContentType(), $mimeType) === 0 && !($part instanceof \Swift_Mime_Attachment)) {
                return $part;
            }
        }

    }//end getMIMEPart()


    /**
     * Convert a Swift Mime Message to a CustomersMailCloud Payload.
     *
     * @param Swift_Mime_SimpleMessage $message
     *
     * @return array
     */
    protected function getMessagePayload(Swift_Mime_SimpleMessage $message)
    {
        $payload = [
            'api_key'  => $this->key,
            'api_user' => $this->apiUser,
        ];

        $this->processRecipients($payload, $message);

        $this->processMessageParts($payload, $message);

        $this->processHeaders($payload, $message);

        return $payload;

    }//end getMessagePayload()


    /**
     * Applies the recipients of the message into the API Payload.
     *
     * @param array                    $payload
     * @param Swift_Mime_SimpleMessage $message
     *
     * @return void
     */
    protected function processRecipients(array &$payload, Swift_Mime_SimpleMessage $message)
    {
        $payload['from'] = current($this->convertEmailsArray($message->getFrom()));
        if (empty($message->getTo()) === false) {
            $payload['to'] = $this->convertEmailsArray($message->getTo());
        }

        $payload['subject'] = $message->getSubject();

        if (empty($message->getCc()) === false) {
            $payload['cc'] = $this->convertEmailsArray($message->getCc());
        }

        if (empty($message->getReplyTo()) === false) {
            $payload['replyto'] = current($this->convertEmailsArray([$message->getReplyTo()]));
        }

        if (empty($message->getBcc()) === false) {
            $payload['bcc'] = $this->convertEmailsArray($message->getBcc());
        }

    }//end processRecipients()


    /**
     * Applies the message parts and attachments
     * into the API Payload.
     *
     * @param array                    $payload
     * @param Swift_Mime_SimpleMessage $message
     *
     * @return void
     */
    protected function processMessageParts(array &$payload, Swift_Mime_SimpleMessage $message)
    {
        //Get the primary message.
        switch ($message->getContentType()) {
        case 'text/html':
        case 'multipart/alternative':
        case 'multipart/mixed':
            $payload['html'] = $message->getBody();
            break;
        default:
            $payload['text'] = $message->getBody();
            break;
        }

        // Provide an alternate view from the secondary parts.
        if ($plain = $this->getMIMEPart($message, 'text/plain')) {
            $payload['text'] = $plain->getBody();
        }

        if ($html = $this->getMIMEPart($message, 'text/html')) {
            $payload['html'] = $html->getBody();
        }

    }//end processMessageParts()


    /**
     * Applies the headers into the API Payload.
     *
     * @param array                    $payload
     * @param Swift_Mime_SimpleMessage $message
     *
     * @return void
     */
    protected function processHeaders(array &$payload, Swift_Mime_SimpleMessage $message)
    {
        $headers = [];
        $headersSetInMessage = [];

        foreach ($message->getHeaders()->getAll() as $key => $value) {
            $fieldName = $value->getFieldName();

            $excludedHeaders = [
                'Message-ID',
                'From',
                'To',
                'Cc',
                'Bcc',
                'Subject',
                'Reply-To',
                'Content-Type',
                'MIME-Version',
                'Date',
                'Content-Transfer-Encoding',
            ];

            if (!in_array($fieldName, $excludedHeaders)) {
                $headersSetInMessage[$fieldName] = true;

                array_push(
                    $headers,
                    [
                        "name"  => $fieldName,
                        "value" => $value->getFieldBody(),
                    ]
                );
            }
        }//end foreach

        foreach ($this->defaultHeaders as $header => $value) {
            if (isset($headersSetInMessage[$header])) {
                continue;
            }

            array_push(
                $headers,
                [
                    "name"  => $header,
                    "value" => $value,
                ]
            );
        }

        $payload['headers'] = $headers;

    }//end processHeaders()


    /**
     * {@inheritdoc}
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);

    }//end registerPlugin()


    /**
     * Get a new HTTP client instance.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        return new Client();

    }//end getHttpClient()


}//end class
