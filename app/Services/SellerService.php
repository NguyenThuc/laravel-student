<?php

namespace App\Services;

use App\Models\SellerPasswordReset;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SellerService {

    const PER_PAGE = 10;
    const ROLE_ADMIN = 1;
    const ROLE_MEMBER = 2;

    public function getAll()
    {
        return Seller::all();
    }

    public function findById($id)
    {
        return Seller::find($id);
    }

    public function getData($where = [], $orWhere = [], $perPage = 10, $orderBy)
    {
        $data = Seller::where(function ($query) use ($where, $orWhere) {
            $query->where($where);
            if (!empty($orWhere))
                $query->orWhere($orWhere);
        });

        if (!empty($orderBy)){
            foreach ($orderBy as $key => $value) {
                $data->orderBy($key, $value);
            }
        }
        return $data->paginate($perPage);
    }

    public function createResetPassword($email, $token)
    {
        try {
            SellerPasswordReset::firstOrCreate([
                'email' => $email,
                'token' => $token,
                'expires_at' => Carbon::now()->addHours(5),
                'created_at' => Carbon::now()
            ]);
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    public function createPassword ($request) {

        try {
            $checkToken = SellerPasswordReset::where(['token' => $request['token']])->first();
            if ($checkToken) {
                Seller::where('email', $checkToken->email)->update([
                    'password' => Hash::make($request['confirm_password'])
                ]);
            }
            SellerPasswordReset::where('email',$checkToken->email)->delete();
            return true;
        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }

    }

    public function forgotPassword($email, $token)
    {
        try {
            $findEmail = SellerPasswordReset::where('email', $email);

            if (!$findEmail->first()) {
                SellerPasswordReset::firstOrCreate([
                    'email' => $email,
                    'token' => $token,
                    'expires_at' => Carbon::now()->addHours(5),
                    'created_at' => Carbon::now()
                ]);
            } else {
                $findEmail->update(['token' => $token]);
            }

            return true;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function checkTokenValid($request)
    {
        $findEmail = SellerPasswordReset::where('token', $request['token'])->first();

        if (!$findEmail) return false;

        return true;
    }

    public function resetPassword($request)
    {
        try {
            $oldSeller = SellerPasswordReset::where('token', $request['token'])->first();

            Seller::where('email', $oldSeller->email)->update([
                'password' => Hash::make($request['confirm_password'])
            ]);

            $query = 'DELETE FROM seller_password_resets where email = ?';
            DB::delete($query, [$oldSeller->email]);
            return true;
        } catch (\Exception $exception) {
            throw $exception;
        }

    }

    public function create($data, $educationalInsIds)
    {
        try {
            DB::beginTransaction();
            $seller = Seller::create($data);
            if (isset($seller)) {
                $seller->educationalInstitutions()->attach($educationalInsIds);
            }
            DB::commit();
            return $seller;
        }
        catch (\PDOException $ex) {
            DB::rollBack();
            return false;
        }
    }

    public function update($id, $data, $educationalInsIds = [])
    {
        $seller = $this->findById($id);
        if ($seller) {
            try {
                DB::beginTransaction();
                $seller->update($data);
                if(!empty($educationalInsIds)) {
                    $seller->educationalInstitutions()->sync($educationalInsIds);
                }
                DB::commit();
                return $seller;
            }
            catch (\PDOException $ex) {
                DB::rollBack();
                return false;
            }
        }
        return false;
    }

    public function findByEmail($email)
    {
        return Seller::where('email', $email)->first();
    }

    public function getByAgencyId($id)
    {
        return Seller::where('agency_id', $id)->get();
    }

    public function getIdEducationalInstitutions($sellerId)
    {
        return Seller::find($sellerId)->educationalInstitutions()->get()->pluck('id')->toArray();
    }

    public function findOrFailById($id)
    {
        return Seller::findOrFail($id);
    }

}
