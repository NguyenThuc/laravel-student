<?php

namespace App\Http\Controllers;

use App\Http\Requests\Seller\DeleteRequest;
use App\Http\Requests\Seller\SettingSellerRequest;
use App\Http\Requests\Seller\UpdateProfileSellerRequest;
use App\Jobs\CreatePasswordJob;
use App\Jobs\UpdateProfileJob;
use App\Models\Seller;
use App\Services\ActivityLogService;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\Seller\ScreenEditorRequest;

class SellerController extends Controller
{

    private $sellerService;

    const REQUEST_SETTING_SELLER = [
        'name',
        'email',
        'role',
        'educationalInsIds',
        'agency_id',
    ];

    const TITLE = 'スタッフ管理 | Edule';
    const COMMON_TITLE = 'Edule';


    public function __construct(SellerService $sellerService)
    {
        $this->sellerService = $sellerService;

    }//end __construct()


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->expectsJson()) {
            $data = $this->getData($request);
            return $this->responseSuccess($data);
        } else {
            return view('seller.index', ['title' => self::TITLE]);
        }

    }//end index()


    public function getData($request)
    {
        $perPage = $this->sellerService::PER_PAGE;
        $orderBy = ['created_at' => 'desc'];
        $wheres = [];
        $orWhere = [];
        if ($request->sortField) {
            $order = $request->sortOrder == 'descend' ? 'desc' : 'asc';
            $orderBy = [
                $request->sortField,
                $order,
            ];
        }

        return $this->sellerService->getData($wheres, $orWhere, $perPage, $orderBy);

    }//end getData()


    public function create(ScreenEditorRequest $request)
    {
        $this->authorize('create', Auth::user());
        $seller = Auth::user()->only(['agency_id']);
        $title = self::TITLE;
        return view('seller.editor', compact('seller', 'title'));

    }//end create()


    public function store(SettingSellerRequest $request)
    {
        $request = $request->only(self::REQUEST_SETTING_SELLER);
        $randNumber = rand(0, 9);
        $password = $randNumber.Str::random(9);
        $educationalInsIds = $request['educationalInsIds'];
        $data = [
            'agency_id' => Auth::guard('seller')->user()->agency_id,
            'name'      => $request['name'],
            'role'      => $request['role'],
            'email'     => $request['email'],
            'password'  => Hash::make($password),
        ];
        $seller = $this->sellerService->create($data, $educationalInsIds);
        $email = $seller->email;
        $token = sha1($email).time();
        if ($seller) {
            $create = $this->sellerService->createResetPassword($email, $token);
            if ($create) {
                $urlRedirect = route('active-seller')."?token=$token";
                try {
                    CreatePasswordJob::dispatch($seller, $urlRedirect);
                    return $this->responseSuccess();
                } catch (\Exception $exception) {
                    return $this->responseError($exception->getMessage());
                }
            }

            return $this->responseError();
        }

        return $this->responseError();

    }//end store()


    public function checkEmailExist(Request $request)
    {
        $email = $request->email;
        $seller = $this->sellerService->findByEmail($email);

        if (!$seller) {
            return $this->responseSuccess();
        }

        if (isset($request->id) && ($seller->id == $request->id)) {
            return $this->responseSuccess();
        }

        return $this->responseError(__('auth.email_exists'), Response::HTTP_UNPROCESSABLE_ENTITY);

    }//end checkEmailExist()


    public function edit(ScreenEditorRequest $request, $id)
    {
        $seller = $this->sellerService->findOrFailById($id);
        if ($seller) {
            $sellerEducationalIns = $seller->educationalInstitutions()->get();
            $title = self::TITLE;
            return view('seller.editor', compact('seller', 'sellerEducationalIns', 'title'));
        }

        abort(404);

    }//end edit()


    public function detail($id)
    {
        $seller = $this->sellerService->findOrFailById($id);
        // TODO: need to be optimized
        $this->authorize('view', Auth::user(), $seller);

        if (!$seller) {
            return redirect()->route('seller.list');
        }

        $title = self::TITLE;
        $seller->roleName = Seller::getRoleName($seller->role);
        $seller->educationalInstitutions = $seller->educationalInstitutions()->get();
        return view('seller.detail', compact('seller', 'title'));

    }//end detail()


    public function update(SettingSellerRequest $request, $id)
    {
        $seller = $this->sellerService->findOrFailById($id);
        $oldData = $seller->toArray();
        $request = $request->only(self::REQUEST_SETTING_SELLER);
        $educationalInsIds = $request['educationalInsIds'];
        $data = [
            'name'  => $request['name'],
            'role'  => $request['role'],
            'email' => $request['email'],
        ];
        $seller = $this->sellerService->update($id, $data, $educationalInsIds);
        if ($seller) {
            return $this->responseSuccess();
        }

        ActivityLogService::error('update', Auth::user()->id, 'Update seller account failed', $oldData, $seller->getChanges());
        return $this->responseError();

    }//end update()


    public function delete(DeleteRequest $request, $id)
    {
        $seller = Seller::where('id', $id)->delete();

        if ($seller) {
            return $this->responseSuccess();
        }

        ActivityLogService::error('delete', Auth::user()->id, 'Delete seller account failed');
        return $this->responseError();

    }//end delete()


    /**
     * Render my page
     *
     * @return \Illuminate\Http\Response
     */
    public function myPage()
    {
        $seller = $this->sellerService->findOrFailById(auth()->user('seller')->id);
        $this->authorize('view', $seller);
        $seller->roleName = Seller::getRoleName($seller->role);
        $seller->educationalInstitutions = $seller->educationalInstitutions()->get()->toArray();

        $title = self::COMMON_TITLE;
        return view('seller.my-page', compact('seller', 'title'));

    }//end myPage()


    public function getSellersByAgency()
    {
        $agencyId = auth()->user('seller')->agency_id;
        $sellers = $this->sellerService->getByAgencyId($agencyId);
        return $this->responseJson(Response::HTTP_OK, 'success', '', null, $sellers);

    }//end getSellersByAgency()


    public function profile()
    {
        $seller = auth('seller')->user();
        $title = self::COMMON_TITLE;
        return view('seller.profile', compact('seller', 'title'));

    }//end profile()


    public function updateProfile(UpdateProfileSellerRequest $request)
    {
        $seller = auth('seller')->user();
        $oldEmail = $seller->email;
        $dataUpdate = $request->only(['name', 'email']);

        $seller = $this->sellerService->update($seller->id, $dataUpdate);

        if ($seller) {
            if ($oldEmail !== $request['email']) {
                UpdateProfileJob::dispatch($request['email'], $seller);
            }

            return $this->responseSuccess();
        }

        return $this->responseError(Response::HTTP_BAD_REQUEST, 'error');

    }//end updateProfile()


}//end class
