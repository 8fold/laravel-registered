<?php

namespace Eightfold\Registered\SiteAddress;

use Eightfold\Registered\ControllerBase;

use Auth;
use Validator as ValidatorFacade;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

use Eightfold\Registered\SiteAddress\UserSite;

use Eightfold\UIKit\UIKit;

class ResourceController extends ControllerBase
{
    public function create($username, Request $request)
    {
        $this->validateSiteAddress($request->all())->validate();

        Auth::user()->profile
            ->addSiteOfType($request->site_address, $request->site_type);

        $alert = UIKit::alert([
            'External site address added',
            'External site added successfully.'
        ])->success();

        return parent::back($alert);
    }

    private function validateSiteAddress(array $data, string $key = null): Validator
    {
        $fieldName = 'site_address';
        if ( ! is_null($key)) {
            $fieldName = $fieldName .'_'. $key;

        }

        return ValidatorFacade::make($data,
                [
                    $fieldName => 'required|url'
                ],
                [
                    $fieldName .'.required' => 'The url field is required',
                    $fieldName .'.url' => 'The url provided is invalid'
                ]
            );
    }

    public function update($username, $publicKey, Request $request)
    {
        $this->validateSiteAddress($request->all(), $publicKey)->validate();

        $fieldName = 'site_address_'. $publicKey;
        $fieldType = 'site_type_'. $publicKey;

        $siteAddy = UserSite::withKey($publicKey);
        $siteAddy->type = $request[$fieldType];
        $siteAddy->address = $request[$fieldName];
        $siteAddy->save();

        $alert = UIKit::alert([
            'External site updated',
            'The external site information was updated.'
        ])->success();

        return parent::back($alert);
    }

    public function delete($username, $publicKey)
    {
        Auth::user()->profile->sites()->withPublicKey($publicKey)->delete();

        $alert = UIKit::alert([
            'External site deleted',
            'The external site was deleted.'
        ])->success();

        return parent::back($alert);
    }
}
