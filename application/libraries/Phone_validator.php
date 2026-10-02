<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Phone_validator Library
 * 
 * Centralized international and India-specific phone validation, parsing, and normalization.
 */
class Phone_validator {

    protected static $instance = null;
    protected $ci;
    protected $metadata;
    protected $countries = [];
    protected $callingCodeMap = [];

    /**
     * Singleton instance accessor
     * 
     * @return Phone_validator
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Static validation facade
     * 
     * @param string $rawNumber
     * @param string $countryCode
     * @param bool $isRequired
     * @return array
     */
    public static function validate($rawNumber, $countryCode = 'IN', $isRequired = true) {
        return self::instance()->validate_and_normalize($rawNumber, $countryCode, $isRequired);
    }

    /**
     * Static normalization facade (returns canonical E.164 or null)
     * 
     * @param string $rawNumber
     * @param string $countryCode
     * @param bool $isRequired
     * @return string|null
     */
    public static function normalize($rawNumber, $countryCode = 'IN', $isRequired = true) {
        $res = self::instance()->validate_and_normalize($rawNumber, $countryCode, $isRequired);
        return $res['valid'] ? $res['normalized'] : null;
    }

    /**
     * Static parsing facade
     * 
     * @param string $storedValue
     * @param string $defaultCountry
     * @return array
     */
    public static function parse($storedValue, $defaultCountry = 'IN') {
        return self::instance()->parse_number($storedValue, $defaultCountry);
    }

    public function __construct() {
        if (function_exists('get_instance')) {
            $this->ci =& get_instance();
        }
        $this->load_metadata();
        self::$instance = $this;
    }

    /**
     * Load libphonenumber metadata from JSON config
     */
    protected function load_metadata() {
        $metadataPath = APPPATH . 'config/phone_metadata.json';
        if (file_exists($metadataPath)) {
            $json = file_get_contents($metadataPath);
            $data = json_decode($json, true);
            if ($data && isset($data['countries'])) {
                $this->metadata = $data;
                $this->countries = $data['countries'];
                $this->callingCodeMap = $data['callingCodeMap'] ?? [];
            }
        }
    }

    /**
     * Check if a number is a valid 10-digit Indian mobile number
     * 
     * @param string $digits
     * @return bool
     */
    public function is_valid_indian_mobile($digits) {
        $digits = preg_replace('/\D/', '', (string)$digits);
        
        // Exactly 10 digits
        if (strlen($digits) !== 10) {
            return false;
        }

        // Must start with 6, 7, 8, or 9
        if (!preg_match('/^[6-9]/', $digits)) {
            return false;
        }

        // Must not be all repeating digits (e.g., 0000000000, 1111111111)
        if (preg_match('/^(\d)\1{9}$/', $digits)) {
            return false;
        }

        return true;
    }

    /**
     * Validate and normalize a phone number
     * 
     * @param string $rawNumber The raw input phone number
     * @param string $countryCode ISO2 country code (default 'IN')
     * @param bool $isRequired Whether the field is required
     * @return array [
     *   'valid' => bool,
     *   'normalized' => string|null (E.164, e.g. +919847011223),
     *   'country' => string (ISO2, e.g. 'IN'),
     *   'calling_code' => string (e.g. '+91'),
     *   'national_number' => string (e.g. '9847011223'),
     *   'error' => string|null
     * ]
     */
    public function validate_and_normalize($rawNumber, $countryCode = 'IN', $isRequired = true) {
        $countryCode = strtoupper(trim((string)$countryCode));
        if (empty($countryCode)) {
            $countryCode = 'IN';
        }

        $rawNumber = trim((string)$rawNumber);

        // Check if empty
        if ($rawNumber === '') {
            if ($isRequired) {
                return [
                    'valid' => false,
                    'normalized' => null,
                    'country' => $countryCode,
                    'calling_code' => $this->get_calling_code($countryCode),
                    'national_number' => '',
                    'error' => 'Phone number is required.'
                ];
            } else {
                return [
                    'valid' => true,
                    'normalized' => null,
                    'country' => $countryCode,
                    'calling_code' => $this->get_calling_code($countryCode),
                    'national_number' => '',
                    'error' => null
                ];
            }
        }

        // If the number starts with '+', attempt to extract country from prefix
        if (strpos($rawNumber, '+') === 0) {
            $parsed = $this->parse_number($rawNumber, $countryCode);
            if ($parsed['valid_country']) {
                $countryCode = $parsed['country'];
                $cleanNational = $parsed['national_number'];
            } else {
                $cleanNational = preg_replace('/[^\d]/', '', $rawNumber);
            }
        } else {
            // Check if countryCode calling code is prefixed without '+' (e.g. '919847011223')
            $callingCode = $this->get_calling_code($countryCode); // e.g. '91' without '+'
            $numericCallingCode = preg_replace('/\D/', '', $callingCode);
            $cleanDigits = preg_replace('/\D/', '', $rawNumber);

            if ($countryCode === 'IN') {
                if (strpos($cleanDigits, '91') === 0 && strlen($cleanDigits) === 12) {
                    $cleanNational = substr($cleanDigits, 2);
                } elseif (strpos($cleanDigits, '0') === 0 && strlen($cleanDigits) === 11) {
                    $cleanNational = substr($cleanDigits, 1);
                } else {
                    $cleanNational = $cleanDigits;
                }
            } else {
                if (!empty($numericCallingCode) && strpos($cleanDigits, $numericCallingCode) === 0 && strlen($cleanDigits) > strlen($numericCallingCode) + 4) {
                    $cleanNational = substr($cleanDigits, strlen($numericCallingCode));
                } else {
                    $cleanNational = $cleanDigits;
                }
            }
        }

        // Clean national prefix if exists (e.g. leading 0 in UK or UAE)
        if ($countryCode !== 'IN') {
            $prefixRegex = $this->countries[$countryCode]['nationalPrefixForParsing'] ?? ($this->countries[$countryCode]['nationalPrefix'] ?? '');
            if ($prefixRegex !== '' && $prefixRegex !== null) {
                $cleanNational = preg_replace('/^(?:' . $prefixRegex . ')/', '', $cleanNational);
            }
        }

        // Country-specific validation
        if ($countryCode === 'IN') {
            if (!$this->is_valid_indian_mobile($cleanNational)) {
                $err = 'Please enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.';
                if (strlen($cleanNational) !== 10) {
                    $err = 'Indian mobile number must be exactly 10 digits.';
                } elseif (!preg_match('/^[6-9]/', $cleanNational)) {
                    $err = 'Indian mobile number must start with 6, 7, 8, or 9.';
                } elseif (preg_match('/^(\d)\1{9}$/', $cleanNational)) {
                    $err = 'Invalid phone number: repeated digits are not allowed.';
                }
                return [
                    'valid' => false,
                    'normalized' => null,
                    'country' => 'IN',
                    'calling_code' => '+91',
                    'national_number' => $cleanNational,
                    'error' => $err
                ];
            }

            return [
                'valid' => true,
                'normalized' => '+91' . $cleanNational,
                'country' => 'IN',
                'calling_code' => '+91',
                'national_number' => $cleanNational,
                'error' => null
            ];
        }

        // International validation using metadata
        $countryMeta = $this->countries[$countryCode] ?? null;
        if (!$countryMeta) {
            // Fallback generic length check between 6 and 15 digits (ITU-T E.164 standard)
            $len = strlen($cleanNational);
            if ($len < 6 || $len > 15) {
                return [
                    'valid' => false,
                    'normalized' => null,
                    'country' => $countryCode,
                    'calling_code' => '+' . ($countryMeta['callingCode'] ?? ''),
                    'national_number' => $cleanNational,
                    'error' => 'Phone number must be between 6 and 15 digits.'
                ];
            }
            $dial = '+' . ($countryMeta['callingCode'] ?? '1');
            return [
                'valid' => true,
                'normalized' => $dial . $cleanNational,
                'country' => $countryCode,
                'calling_code' => $dial,
                'national_number' => $cleanNational,
                'error' => null
            ];
        }

        $callingCode = '+' . $countryMeta['callingCode'];
        $lengths = $countryMeta['lengths'] ?? [];
        $len = strlen($cleanNational);

        // Check lengths if metadata has them
        if (!empty($lengths) && !in_array($len, $lengths)) {
            return [
                'valid' => false,
                'normalized' => null,
                'country' => $countryCode,
                'calling_code' => $callingCode,
                'national_number' => $cleanNational,
                'error' => "Invalid phone number length for {$countryCode} (expected " . implode(' or ', $lengths) . " digits, got {$len})."
            ];
        }

        // Check national regex if metadata has it
        $pattern = $countryMeta['nationalRegex'] ?? '';
        if (!empty($pattern)) {
            // Safely evaluate regex
            if (@preg_match('/^(?:' . $pattern . ')$/x', $cleanNational) === 0) {
                return [
                    'valid' => false,
                    'normalized' => null,
                    'country' => $countryCode,
                    'calling_code' => $callingCode,
                    'national_number' => $cleanNational,
                    'error' => "The phone number is not valid for {$countryCode}."
                ];
            }
        }

        return [
            'valid' => true,
            'normalized' => $callingCode . $cleanNational,
            'country' => $countryCode,
            'calling_code' => $callingCode,
            'national_number' => $cleanNational,
            'error' => null
        ];
    }

    /**
     * Parse an existing/stored phone number into country, calling code, and national number.
     * 
     * @param string $storedValue
     * @param string $defaultCountry
     * @return array
     */
    public function parse_number($storedValue, $defaultCountry = 'IN') {
        $storedValue = trim((string)$storedValue);
        $defaultCountry = strtoupper($defaultCountry);

        if (empty($storedValue)) {
            return [
                'valid_country' => false,
                'country' => $defaultCountry,
                'calling_code' => $this->get_calling_code($defaultCountry),
                'national_number' => '',
                'e164' => '',
                'formatted' => ''
            ];
        }

        // If stored with '+'
        if (strpos($storedValue, '+') === 0) {
            $digits = preg_replace('/\D/', '', substr($storedValue, 1));
            
            // Match calling code from map (sort calling codes descending by length to match e.g. 971 before 97 or 1242 before 1)
            $matchedCountry = null;
            $matchedDialCode = null;
            $matchedNational = null;

            // Check if calling code matches default country first (e.g. +91)
            $defaultDial = preg_replace('/\D/', '', $this->get_calling_code($defaultCountry));
            if (!empty($defaultDial) && strpos($digits, $defaultDial) === 0) {
                $matchedCountry = $defaultCountry;
                $matchedDialCode = '+' . $defaultDial;
                $matchedNational = substr($digits, strlen($defaultDial));
            } else {
                // Check lengths 4, 3, 2, 1
                for ($len = 4; $len >= 1; $len--) {
                    $prefix = substr($digits, 0, $len);
                    if (isset($this->callingCodeMap[$prefix])) {
                        $matchedCountry = $this->callingCodeMap[$prefix][0];
                        $matchedDialCode = '+' . $prefix;
                        $matchedNational = substr($digits, $len);
                        break;
                    }
                }
            }

            if ($matchedCountry) {
                return [
                    'valid_country' => true,
                    'country' => $matchedCountry,
                    'calling_code' => $matchedDialCode,
                    'national_number' => $matchedNational,
                    'e164' => '+' . $digits,
                    'formatted' => $matchedDialCode . ' ' . $matchedNational
                ];
            }
        }

        // Clean digits without '+'
        $cleanDigits = preg_replace('/\D/', '', $storedValue);

        // Legacy 10 digits -> assumed Indian number
        if (strlen($cleanDigits) === 10) {
            return [
                'valid_country' => true,
                'country' => 'IN',
                'calling_code' => '+91',
                'national_number' => $cleanDigits,
                'e164' => '+91' . $cleanDigits,
                'formatted' => '+91 ' . $cleanDigits
            ];
        }

        // Legacy 11 digits starting with 0 -> Indian number
        if (strlen($cleanDigits) === 11 && strpos($cleanDigits, '0') === 0) {
            $nat = substr($cleanDigits, 1);
            return [
                'valid_country' => true,
                'country' => 'IN',
                'calling_code' => '+91',
                'national_number' => $nat,
                'e164' => '+91' . $nat,
                'formatted' => '+91 ' . $nat
            ];
        }

        // Legacy 12 digits starting with 91 -> Indian number
        if (strlen($cleanDigits) === 12 && strpos($cleanDigits, '91') === 0) {
            $nat = substr($cleanDigits, 2);
            return [
                'valid_country' => true,
                'country' => 'IN',
                'calling_code' => '+91',
                'national_number' => $nat,
                'e164' => '+91' . $nat,
                'formatted' => '+91 ' . $nat
            ];
        }

        // Fallback default
        $dial = $this->get_calling_code($defaultCountry);
        return [
            'valid_country' => false,
            'country' => $defaultCountry,
            'calling_code' => $dial,
            'national_number' => $cleanDigits,
            'e164' => $storedValue,
            'formatted' => $storedValue
        ];
    }

    /**
     * Get international calling code for country
     */
    public function get_calling_code($countryCode) {
        $code = strtoupper($countryCode);
        if (isset($this->countries[$code]['callingCode'])) {
            return '+' . $this->countries[$code]['callingCode'];
        }
        return '+91';
    }
}
