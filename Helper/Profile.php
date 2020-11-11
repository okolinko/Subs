<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 9/29/16
 * Time: 5:49 PM
 */

namespace Toppik\Subscriptions\Helper;


use Toppik\Subscriptions\Model\Profile as ProfileModel;

class Profile
{

    /**
     * @param ProfileModel $profile
     * @param ProfileModel[] $allProfiles
     * @return ProfileModel[]
     */
    public function findSimilarProfiles(ProfileModel $profile, $allProfiles) {
        $similarProfiles = [];
        foreach($allProfiles as $comparable) {
            /* @var Profile $comparable */
            if($this->profilesAreSimilar($profile, $comparable)) {
                $similarProfiles[] = $comparable;
            }
        }
        return $similarProfiles;
    }

    /**
     * @param Profile $p1
     * @param Profile $p2
     * @return bool
     */
    public function profilesAreSimilar(ProfileModel $p1, ProfileModel $p2) {
        if($p1->getId() == $p2->getId()) {
            return false;
        }
        if($p1->getCustomerId() != $p2->getCustomerId()) {
            return false;
        }
        if($p1->getPaymentTokenId() != $p2->getPaymentTokenId()) {
            return false;
        }
        if(! $this->compareShippingMethod($p1, $p2)) {
            return false;
        }
        if(! $this->compareShippingAddress($p1, $p2)) {
            return false;
        }
        return true;
    }

    /**
     * @param ProfileModel $p1
     * @param ProfileModel $p2
     * @return bool
     */
    private function compareShippingMethod(ProfileModel $p1, ProfileModel $p2)
    {
        $shipping1 = $p1->getShippingAddress();
        $shipping2 = $p2->getShippingAddress();
        if($shipping1->getShippingMethod() != $shipping2->getShippingMethod()) {
            return false;
        }
        return true;
    }

    /**
     * @param ProfileModel $p1
     * @param ProfileModel $p2
     * @return bool
     */
    private function compareShippingAddress(ProfileModel $p1, ProfileModel $p2)
    {
        $shipping1 = $p1->getShippingAddress();
        $shipping2 = $p2->getShippingAddress();
        if(
            ($shipping1->getFirstname() != $shipping2->getFirstname())
            or 
            ($shipping1->getLastname() != $shipping2->getLastname())
            or 
            ($shipping1->getStreet() != $shipping2->getStreet())
            or
            ($shipping1->getCity() != $shipping2->getCity())
            or
            ($shipping1->getRegion() != $shipping2->getRegion())
            or
            ($shipping1->getRegionId() != $shipping2->getRegionId())
            or
            ($shipping1->getPostcode() != $shipping2->getPostcode())
            or
            ($shipping1->getCountryId() != $shipping2->getCountryId())
            or
            ($shipping1->getTelephone() != $shipping2->getTelephone())
        ) {
            return false;
        }
        return true;
    }

}