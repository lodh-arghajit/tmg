<?php

namespace Drupal\tmg_utility;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use Giggsey\Locale\Locale;

/**
 * Provides a default implementation for phone to country conversion.
 */
class PhoneToCountryConverter {

  /**
   * Returns the customary display name in the given language for the given territory the phone
   * number is from. If it could be from many territories, nothing is returned.
   *
   * @param string $number
   * @param string $local
   * @return string
   */
  public static function getCountryNameFromPhone(string $number, string $local): string
  {
    try {
      $phoneUtils = PhoneNumberUtil::getInstance();
      $number = $phoneUtils->parse($number, NULL);
      $region = $phoneUtils->getRegionCodeForNumber($number);
      return Locale::getDisplayRegion(
        '-' . $region,
        "en"
      );
    }
    catch (\Exception $e) {
      return '';
    }
  }


}
