/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/wecodeart/wp-content/themes/wecodeart/assets/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/js/admin/notifications/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/admin/notifications/index.js":
/*!*********************************************!*\
  !*** ./src/js/admin/notifications/index.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt FrameWork
 * @subpackage 	Admin/Notifications
 * @copyright   Copyright (c) 2020, WeCodeArt Framework
 * @since 		3.8.1
 * @version		3.8.1
 */
(function ($) {
  /**
   * Handles admin notification dismiss/transients.
   *
   * @since 3.8.1
   */
  Notification = {
    /**
     * Initialize
     *
     * @since 	3.8.1
     * @method 	init
     */
    init: function init() {
      this._bind();
    },

    /**
     * Binds events for notification buttons
     *
     * @since 	3.8.1
     * @access 	private
     * @method 	_bind
     */
    _bind: function _bind() {
      $(document).on('click', '.wca-notice__close', Notification._dismissNew);
      $(document).on('click', '.wca-notice .notice-dismiss', Notification._dismiss);
    },
    _dismiss: function _dismiss(e) {
      e.preventDefault();
      var repeat_after = $(this).parents('.wca-notice').data('repeat') || '';
      var notice_id = $(this).parents('.wca-notice').attr('id') || '';

      Notification._ajax(notice_id, repeat_after);
    },
    _dismissNew: function _dismissNew(e) {
      e.preventDefault();
      var repeat_after = $(this).data('repeat') || '';
      var notice_id = $(this).parents('.wca-notice').attr('id') || '';
      var $el = $(this).parents('.wca-notice');
      $el.fadeTo(100, 0, function () {
        return $el.slideUp(100, function () {
          return $el.remove();
        });
      });

      Notification._ajax(notice_id, repeat_after);

      var link = $(this).attr('href') || '';
      var target = $(this).attr('target') || '';

      if ('' !== link && '_blank' === target) {
        window.open(link, '_blank');
      }
    },
    _ajax: function _ajax(notice_id, repeat_after) {
      if ('' === notice_id) {
        return;
      }

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'wecodeart_notification_dismiss',
          notice_id: notice_id,
          repeat_after: parseInt(repeat_after)
        }
      });
    }
  };
  $(function () {
    return Notification.init();
  });
})(jQuery);

/***/ })

/******/ });
//# sourceMappingURL=index.js.map