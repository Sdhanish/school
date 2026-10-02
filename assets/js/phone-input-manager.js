/**
 * Centralized Phone Input Manager (PhoneManager)
 * 
 * Reusable, centralized phone number system across Login2 School Management Software.
 * Provides:
 * - Single intl-tel-input integration
 * - India (+91) default configuration
 * - Declarative HTML initialization (data-phone-field="true" or .phone-input)
 * - Live formatting while typing (98470 11223 display, +919847011223 canonical storage)
 * - Safe country-change handling (avoids corrupt number combinations)
 * - Country detection on edit forms
 * - Centralized form submit normalization to canonical E.164
 * - Independent state per phone input
 */
(function(window, document) {
    'use strict';

    // Track initialized input instances and forms
    const instances = new Map();
    const boundForms = new WeakSet();

    // Default configuration
    const CONFIG = {
        defaultCountry: 'in',
        preferredCountries: ['in', 'ae', 'sa', 'qa', 'kw', 'om', 'us', 'gb'],
        selector: 'input[data-phone-field="true"], input[data-phone-input="true"], input.phone-input'
    };

    /**
     * Resolve target into a native HTMLElement or document
     */
    function resolveElement(target) {
        if (!target) return null;
        if (target === document || target === window) return document;
        if (typeof target === 'string') return document.querySelector(target);
        if (target.jquery && target.length > 0) return target[0];
        if (target instanceof Node) return target;
        return null;
    }

    /**
     * Cross-version intl-tel-input country getter
     */
    function getCountryData(iti) {
        if (!iti) return null;
        if (typeof iti.getSelectedCountry === 'function') {
            return iti.getSelectedCountry();
        }
        if (typeof iti.getSelectedCountryData === 'function') {
            return iti.getSelectedCountryData();
        }
        return null;
    }

    /**
     * Cross-version intl-tel-input country setter
     */
    function setCountry(iti, iso2) {
        if (!iti || !iso2) return;
        const code = iso2.toLowerCase();
        if (typeof iti.setSelectedCountry === 'function') {
            iti.setSelectedCountry(code);
        } else if (typeof iti.setCountry === 'function') {
            iti.setCountry(code);
        }
    }

    /**
     * Get formatNumberAsYouType function from intlTelInput.utils
     */
    function formatAsYouType(rawDigits, iso2) {
        if (window.intlTelInput && window.intlTelInput.utils && typeof window.intlTelInput.utils.formatNumberAsYouType === 'function') {
            try {
                return window.intlTelInput.utils.formatNumberAsYouType(rawDigits, iso2 || CONFIG.defaultCountry);
            } catch (e) {
                return rawDigits;
            }
        }
        return rawDigits;
    }

    /**
     * Core Phone Instance Constructor
     */
    function PhoneFieldInstance(input, userOptions) {
        this.input = input;
        
        // Declarative options from data attributes or params
        const dataset = input.dataset || {};
        this.options = Object.assign({
            initialCountry: dataset.defaultCountry || dataset.initialCountry || CONFIG.defaultCountry,
            preferredCountries: dataset.preferredCountries ? dataset.preferredCountries.split(',') : CONFIG.preferredCountries,
            separateDialCode: true,
            required: input.hasAttribute('required') || dataset.required === 'true',
            fieldName: input.getAttribute('name') || input.id || 'phone'
        }, userOptions || {});

        this.previousCountryIso = this.options.initialCountry.toLowerCase();
        this.init();
    }

    PhoneFieldInstance.prototype.init = function() {
        const self = this;
        const input = this.input;

        if (!window.intlTelInput) {
            console.error('[PhoneManager] intlTelInput library is not loaded.');
            return;
        }

        // Add styling class
        input.classList.add('phone-intl-input');

        // Synchronized hidden field for country code
        let countryInput = document.getElementById(input.id + '_country') || 
                           (input.form ? input.form.querySelector(`input[name="${this.options.fieldName}_country"]`) : null);
        if (!countryInput && input.form) {
            countryInput = document.createElement('input');
            countryInput.type = 'hidden';
            countryInput.id = (input.id ? input.id + '_country' : this.options.fieldName + '_country');
            countryInput.name = this.options.fieldName + '_country';
            countryInput.value = this.options.initialCountry.toUpperCase();
            input.parentNode.insertBefore(countryInput, input.nextSibling);
        }
        this.countryInput = countryInput;

        // Initialize intl-tel-input once (using modern options matching v29)
        this.iti = window.intlTelInput(input, {
            initialCountry: this.options.initialCountry,
            countryOrder: this.options.preferredCountries,
            separateDialCode: true,
            countrySearch: true
        });

        // Initialize or bind error feedback container
        let feedback = input.parentNode.querySelector('.phone-feedback') || 
                       (input.closest('.form-group') ? input.closest('.form-group').querySelector('.phone-feedback') : null) ||
                       (input.closest('div') ? input.closest('div').querySelector('.phone-feedback') : null);
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'phone-feedback text-danger text-error text-[11px] mt-1';
            feedback.style.display = 'none';
            if (input.parentNode.classList.contains('iti')) {
                input.parentNode.parentNode.appendChild(feedback);
            } else {
                input.parentNode.appendChild(feedback);
            }
        }
        this.feedback = feedback;

        // Handle initial value if present (Edit forms or server repopulation)
        const initialVal = input.value.trim();
        if (initialVal) {
            this.setInitialNumber(initialVal);
        }

        // Store initial country
        const initialCountryData = getCountryData(this.iti);
        if (initialCountryData && initialCountryData.iso2) {
            this.previousCountryIso = initialCountryData.iso2.toLowerCase();
            if (this.countryInput) {
                this.countryInput.value = initialCountryData.iso2.toUpperCase();
            }
        }

        // Bind input events
        this.bindEvents();

        // Bind form submit guard if part of a form
        if (input.form) {
            PhoneManager.bindFormSubmit(input.form);
        }
    };

    /**
     * Get clean national digits (never contains country dial code)
     */
    PhoneFieldInstance.prototype.getNationalNumber = function() {
        const raw = this.input.value.trim();
        let clean = raw.replace(/\D/g, '');
        const country = getCountryData(this.iti);
        const dialCode = (country && country.dialCode) ? country.dialCode.replace(/\D/g, '') : '';
        const iso2 = (country && country.iso2) ? country.iso2.toLowerCase() : 'in';

        if (dialCode && clean.startsWith(dialCode) && clean.length > 10) {
            clean = clean.slice(dialCode.length);
        } else if (raw.startsWith('+') && dialCode && clean.startsWith(dialCode)) {
            clean = clean.slice(dialCode.length);
        } else if (iso2 === 'in' && clean.length === 11 && clean.startsWith('0')) {
            clean = clean.slice(1);
        }
        return clean;
    };

    /**
     * Parse and populate existing stored number on edit form load
     */
    PhoneFieldInstance.prototype.setInitialNumber = function(initialVal) {
        if (!initialVal) return;

        if (initialVal.startsWith('+')) {
            // Canonical E.164 stored format (+919847011223 or +971501234567)
            this.iti.setNumber(initialVal);
        } else if (/^\d{10}$/.test(initialVal)) {
            // Legacy 10-digit Indian number without country prefix
            setCountry(this.iti, 'in');
            this.input.value = initialVal;
        } else {
            this.iti.setNumber(initialVal);
        }

        // Format the visible display number with national digits ONLY (never E.164)
        const country = getCountryData(this.iti);
        const iso2 = (country && country.iso2) ? country.iso2.toLowerCase() : 'in';
        const cleanDigits = this.getNationalNumber();
        if (cleanDigits) {
            this.input.value = formatAsYouType(cleanDigits, iso2);
        }

        if (this.countryInput && country && country.iso2) {
            this.countryInput.value = country.iso2.toUpperCase();
        }

        // Validate state quietly without forcing error display unless invalid and required
        const res = this.validate();
        if (res.isValid && this.input.value.trim() !== '') {
            this.input.classList.add('is-valid');
        }
    };

    /**
     * Bind input, blur, and countrychange events
     */
    PhoneFieldInstance.prototype.bindEvents = function() {
        const self = this;
        const input = this.input;

        // Live formatting on typing
        input.addEventListener('input', function(e) {
            self.handleLiveInput(e);
        });

        // Validate on blur
        input.addEventListener('blur', function() {
            const res = self.validate();
            self.updateVisualState(res);
        });

        // Handle country change
        input.addEventListener('countrychange', function() {
            self.handleCountryChange();
        });
    };

    /**
     * Handle live formatting while typing: ensure visible input contains only national digits
     */
    PhoneFieldInstance.prototype.handleLiveInput = function(e) {
        const country = getCountryData(this.iti);
        const iso2 = (country && country.iso2) ? country.iso2.toLowerCase() : 'in';
        const raw = this.input.value;
        const dialCode = (country && country.dialCode) ? country.dialCode.replace(/\D/g, '') : '';

        // Clean digits and strip country dial code if typed or pasted with prefix
        let cleanDigits = raw.replace(/\D/g, '');

        if (dialCode && cleanDigits.startsWith(dialCode) && cleanDigits.length > 10) {
            cleanDigits = cleanDigits.slice(dialCode.length);
        } else if (raw.trim().startsWith('+') && dialCode && cleanDigits.startsWith(dialCode)) {
            cleanDigits = cleanDigits.slice(dialCode.length);
        } else if (iso2 === 'in' && cleanDigits.length === 11 && cleanDigits.startsWith('0')) {
            cleanDigits = cleanDigits.slice(1);
        }

        // If user is actively typing or pasting, format as you type
        if (cleanDigits.length > 0) {
            const cursorPos = this.input.selectionStart || raw.length;
            const digitsBeforeCursor = raw.slice(0, cursorPos).replace(/\D/g, '').length;

            const formatted = formatAsYouType(cleanDigits, iso2);
            if (formatted !== raw) {
                this.input.value = formatted;

                // Restore cursor position relative to digits
                let newPos = 0;
                let digitsCount = 0;
                for (let i = 0; i < formatted.length; i++) {
                    if (/\d/.test(formatted[i])) {
                        digitsCount++;
                    }
                    newPos = i + 1;
                    if (digitsCount >= digitsBeforeCursor) {
                        break;
                    }
                }
                if (typeof this.input.setSelectionRange === 'function') {
                    this.input.setSelectionRange(newPos, newPos);
                }
            }
        }

        // If currently in error state, revalidate quietly to clear error once valid
        if (this.input.classList.contains('is-invalid') || this.input.classList.contains('!border-error')) {
            const res = this.validate();
            if (res.isValid) {
                this.updateVisualState(res);
            }
        }
    };

    /**
     * Handle safe country changes: prevent corrupt combinations
     */
    PhoneFieldInstance.prototype.handleCountryChange = function() {
        const country = getCountryData(this.iti);
        const newIso = (country && country.iso2) ? country.iso2.toLowerCase() : 'in';

        if (this.countryInput && country && country.iso2) {
            this.countryInput.value = country.iso2.toUpperCase();
        }

        // If country actually changed
        if (this.previousCountryIso !== newIso) {
            const currentVal = this.input.value.trim();
            if (currentVal !== '') {
                // Check if current number can be a valid number in the new country
                const res = this.validate();
                if (!res.isValid) {
                    // Safe behavior: do NOT combine old local number with new country dial code!
                    // Clear the mismatched number so user enters the correct number for the new country.
                    this.input.value = '';
                    this.updateVisualState({
                        isValid: !this.options.required,
                        error: this.options.required ? 'Please enter a valid phone number for ' + (country.name || newIso.toUpperCase()) + '.' : null,
                        country: newIso.toUpperCase(),
                        number: ''
                    });
                } else {
                    // Reformat for the new country
                    const clean = currentVal.replace(/\D/g, '');
                    this.input.value = formatAsYouType(clean, newIso);
                    this.updateVisualState(res);
                }
            }
            this.previousCountryIso = newIso;
        }
    };

    /**
     * Comprehensive Validation
     */
    PhoneFieldInstance.prototype.validate = function() {
        const input = this.input;
        const val = input.value.trim();
        const selectedCountry = getCountryData(this.iti);
        const countryIso = (selectedCountry && selectedCountry.iso2) ? selectedCountry.iso2.toUpperCase() : 'IN';
        const cleanDigits = val.replace(/\D/g, '');

        if (this.countryInput) {
            this.countryInput.value = countryIso;
        }

        // Empty field handling
        if (val === '') {
            if (this.options.required) {
                return {
                    isValid: false,
                    error: 'Phone number is required.',
                    country: countryIso,
                    number: ''
                };
            }
            return {
                isValid: true,
                error: null,
                country: countryIso,
                number: ''
            };
        }

        // India mobile validation rules (+91)
        if (countryIso === 'IN') {
            if (cleanDigits.length !== 10) {
                return {
                    isValid: false,
                    error: 'Please enter a valid 10-digit Indian mobile number.',
                    country: 'IN',
                    number: val
                };
            }

            if (!/^[6-9]/.test(cleanDigits)) {
                return {
                    isValid: false,
                    error: 'Indian mobile number must start with 6, 7, 8, or 9.',
                    country: 'IN',
                    number: val
                };
            }

            // Repeated digits check (e.g. 0000000000, 1111111111, ..., 9999999999)
            if (/^(\d)\1{9}$/.test(cleanDigits)) {
                return {
                    isValid: false,
                    error: 'Invalid mobile number: repeating digits are not allowed.',
                    country: 'IN',
                    number: val
                };
            }

            return {
                isValid: true,
                error: null,
                country: 'IN',
                number: '+91' + cleanDigits,
                nationalNumber: cleanDigits
            };
        }

        // International validation using intl-tel-input
        if (this.iti.isValidNumber && !this.iti.isValidNumber()) {
            const errorCode = (typeof this.iti.getValidationError === 'function') ? this.iti.getValidationError() : null;
            let errorMsg = 'Please enter a valid phone number for ' + (selectedCountry.name || countryIso) + '.';
            if (errorCode === 1) errorMsg = 'Invalid country code.';
            else if (errorCode === 2) errorMsg = 'Phone number is too short.';
            else if (errorCode === 3) errorMsg = 'Phone number is too long.';

            return {
                isValid: false,
                error: errorMsg,
                country: countryIso,
                number: val
            };
        }

        return {
            isValid: true,
            error: null,
            country: countryIso,
            number: this.iti.getNumber(),
            nationalNumber: cleanDigits
        };
    };

    /**
     * Update CSS classes and feedback text
     */
    PhoneFieldInstance.prototype.updateVisualState = function(validationResult) {
        if (!validationResult.isValid && validationResult.error) {
            this.input.classList.add('is-invalid', '!border-error');
            this.input.classList.remove('is-valid');
            this.feedback.textContent = validationResult.error;
            this.feedback.style.display = 'block';
        } else {
            this.input.classList.remove('is-invalid', '!border-error');
            if (this.input.value.trim() !== '') {
                this.input.classList.add('is-valid');
            } else {
                this.input.classList.remove('is-valid');
            }
            this.feedback.textContent = '';
            this.feedback.style.display = 'none';
        }
    };

    /**
     * Public instance methods
     */
    PhoneFieldInstance.prototype.isValid = function() {
        const res = this.validate();
        this.updateVisualState(res);
        return res.isValid;
    };

    PhoneFieldInstance.prototype.getNumber = function() {
        const res = this.validate();
        if (res.isValid && res.number) {
            return res.number;
        }
        return this.iti.getNumber() || (this.input.value.trim() ? this.input.value.trim() : '');
    };

    PhoneFieldInstance.prototype.getCountry = function() {
        const country = getCountryData(this.iti);
        return country && country.iso2 ? country.iso2.toUpperCase() : 'IN';
    };

    PhoneFieldInstance.prototype.setNumber = function(number) {
        if (number) {
            this.setInitialNumber(number);
        } else {
            this.reset();
        }
    };

    PhoneFieldInstance.prototype.reset = function() {
        setCountry(this.iti, this.options.initialCountry);
        this.input.value = '';
        this.input.classList.remove('is-invalid', '!border-error', 'is-valid');
        this.feedback.style.display = 'none';
        this.feedback.textContent = '';
    };

    /**
     * Centralized PhoneManager Object
     */
    const PhoneManager = {
        /**
         * Initialize phone inputs on an element, container, or query selector
         * Accepts: CSS selector string, HTMLElement, jQuery object ($('#form')), or document
         * 
         * @param {string|HTMLElement|jQuery} [target] Selector or element.
         * @param {Object} [opts] Options
         * @returns {PhoneFieldInstance|Array<PhoneFieldInstance>}
         */
        init: function(target, opts) {
            if (!target || target === document) {
                return PhoneManager.initContainer(document, opts);
            }

            const el = resolveElement(target);
            if (!el) return null;

            if (el.tagName === 'INPUT') {
                return PhoneManager.initInput(el, opts);
            } else {
                return PhoneManager.initContainer(el, opts);
            }
        },

        /**
         * Initialize a single input element with guard
         */
        initInput: function(input, opts) {
            const el = resolveElement(input);
            if (!el || el.tagName !== 'INPUT') return null;

            // Initialization guard: prevent duplicate initialization
            if (instances.has(el)) {
                return instances.get(el);
            }

            const instance = new PhoneFieldInstance(el, opts);
            instances.set(el, instance);
            return instance;
        },

        /**
         * Initialize all phone inputs within a container (e.g. form, modal)
         */
        initContainer: function(container, opts) {
            const root = resolveElement(container) || document;
            if (!root || typeof root.querySelectorAll !== 'function') return [];
            const elements = root.querySelectorAll(CONFIG.selector);
            const results = [];

            elements.forEach(function(el) {
                const inst = PhoneManager.initInput(el, opts);
                if (inst) results.push(inst);
            });

            return results;
        },

        /**
         * Get instance by input element, jQuery object, or selector
         */
        getInstance: function(inputOrSelector) {
            const el = resolveElement(inputOrSelector);
            return el ? (instances.get(el) || null) : null;
        },

        /**
         * Validate an input directly using its manager instance
         */
        validate: function(target) {
            const el = resolveElement(target);
            if (!el) return true;
            const inst = PhoneManager.getInstance(el) || PhoneManager.init(el);
            if (!inst) return true;
            return inst.isValid();
        },

        /**
         * Get canonical E.164 normalized number for an input
         */
        getNormalizedNumber: function(target) {
            const el = resolveElement(target);
            if (!el) return '';
            const inst = PhoneManager.getInstance(el) || PhoneManager.init(el);
            if (!inst) {
                return el.value ? el.value.trim() : '';
            }
            return inst.getNumber();
        },

        /**
         * Validate all phone fields inside a form or container
         * Supports jQuery objects, DOM elements, or string selectors.
         * 
         * @param {HTMLFormElement|jQuery|string} form Form element, jQuery object, or selector
         * @returns {boolean} True if all valid
         */
        validateForm: function(form) {
            const formEl = resolveElement(form);
            if (!formEl || typeof formEl.querySelectorAll !== 'function') return true;

            const inputs = formEl.querySelectorAll(CONFIG.selector);
            let allValid = true;
            let firstInvalid = null;

            inputs.forEach(function(el) {
                const inst = PhoneManager.getInstance(el) || PhoneManager.init(el);
                if (inst && !inst.isValid()) {
                    allValid = false;
                    if (!firstInvalid) firstInvalid = el;
                }
            });

            if (!allValid && firstInvalid) {
                firstInvalid.focus();
            }

            return allValid;
        },

        /**
         * Prepare form for submission: validate and sync country codes without polluting visible input with +91
         * 
         * @param {HTMLFormElement|jQuery|string} form
         * @returns {boolean}
         */
        prepareSubmit: function(form) {
            const formEl = resolveElement(form);
            if (!formEl || typeof formEl.querySelectorAll !== 'function') return true;

            if (!PhoneManager.validateForm(formEl)) {
                return false;
            }

            // Sync hidden country fields and ensure visible inputs contain only clean national digits
            const inputs = formEl.querySelectorAll(CONFIG.selector);
            inputs.forEach(function(el) {
                const inst = PhoneManager.getInstance(el);
                if (inst) {
                    if (inst.countryInput) {
                        inst.countryInput.value = inst.getCountry();
                    }
                    if (el.value.trim() !== '') {
                        const cleanDigits = inst.getNationalNumber();
                        const country = getCountryData(inst.iti);
                        const iso2 = (country && country.iso2) ? country.iso2.toLowerCase() : 'in';
                        el.value = formatAsYouType(cleanDigits, iso2);
                    } else {
                        el.value = '';
                    }
                }
            });

            return true;
        },

        /**
         * Attach automated submit interception once per form
         */
        bindFormSubmit: function(form) {
            const formEl = resolveElement(form);
            if (!formEl || boundForms.has(formEl)) return;
            boundForms.add(formEl);

            formEl.addEventListener('submit', function(e) {
                const isValid = PhoneManager.prepareSubmit(formEl);
                if (!isValid) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        },

        /**
         * Auto-initialize all inputs matching CONFIG.selector in DOM
         */
        autoInit: function() {
            PhoneManager.initContainer(document);
        }
    };

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', PhoneManager.autoInit);
    } else {
        PhoneManager.autoInit();
    }

    // Expose global aliases for full compatibility and conceptual architecture
    window.PhoneManager = PhoneManager;
    window.PhoneInputManager = PhoneManager;
    window.initializePhoneFields = function(container, opts) {
        return PhoneManager.init(container, opts);
    };

})(window, document);
