/*!
 * JavaScript Cookie v2.2.1 (fixed for AMD environments)
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
;(function () {
	function extend () {
		var i = 0;
		var result = {};
		for (; i < arguments.length; i++) {
			var attributes = arguments[i];
			for (var key in attributes) {
				result[key] = attributes[key];
			}
		}
		return result;
	}

	function init (converter) {
		function api (key, value, attributes) {
			var result;
			if (arguments.length > 1) {
				attributes = extend({
					path: '/'
				}, api.defaults, attributes);

				if (typeof attributes.expires === 'number') {
					var expires = new Date();
					expires.setMilliseconds(expires.getMilliseconds() + attributes.expires * 864e+5);
					attributes.expires = expires;
				}

				try {
					result = JSON.stringify(value);
					if (/^[\{\[]/.test(result)) {
						value = result;
					}
				} catch (e) {}

				if (!converter.write) {
					value = encodeURIComponent(String(value))
						.replace(/%(23|24|26|2B|3A|3C|3E|3F|5B|5E|60|7C)/g, decodeURIComponent)
						.replace(/[\(\)]/g, escape);
				} else {
					value = converter.write(value, key);
				}

				document.cookie = encodeURIComponent(key) + '=' + value + '; expires=' + attributes.expires.toUTCString() + '; path=' + attributes.path + '; SameSite=Lax';

				if (attributes.domain) {
					document.cookie += '; domain=' + attributes.domain;
				}
				if (attributes.secure) {
					document.cookie += '; secure';
				}
			}

			result = converter.read ? converter.read(value, key) : value;

			try {
				result = JSON.parse(result);
			} catch (e) {}

			return result;
		}

		api.set = api;

		api.get = function (key) {
			return api(key);
		};

		api.getJSON = function (key) {
			return api(key, true);
		};

		api.defaults = {};

		api.remove = function (key, attributes) {
			api(key, '', extend(attributes, { expires: -1 }));
		};

		api.withConverter = init;

		return api;
	}

	var OldCookies = window.Cookies;
	var api = window.Cookies = init(function () {});
	api.noConflict = function () {
		window.Cookies = OldCookies;
		return api;
	};
}());
