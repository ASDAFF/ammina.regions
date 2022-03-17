(function ($) {

	var methods = {
		init: function (arResultInfo, arParamsInfo) {
			methods.self = this;
			methods.arResultInfo = arResultInfo;
			methods.arParamsInfo = arParamsInfo;
			$("body").prepend($(".bam-multiregions-confirm"));
			$("body").append($(".bam-multiregions-popup"));
			$("body").append($(".bam-multiregions-popupbg"));
			methods.divMain = $(this);
			methods.divConfirm = $(".bam-multiregions-confirm");
			methods.divPopup = $(".bam-multiregions-popup");
			methods.divPopupBg = $(".bam-multiregions-popupbg");
			if (methods.arParamsInfo['CHANGE_CITY_MANUAL'] != "Y") {
				return;
			}
			if (methods.arParamsInfo['CITY_VERIFYCATION'] == "Y" && methods.arResultInfo['CONFIRM_REQUEST_SHOW']) {
				methods._showConfirmCityWindow();
				$(methods.divConfirm).on("click", ".bam-multiregions-confirm-button-yes", function () {
					methods._clickConfirmButtonYes(this);
					return false;
				});
				$(methods.divConfirm).on("click", ".bam-multiregions-confirm-button-no", function () {
					methods._clickConfirmButtonNo(this);
					return false;
				});
			}
			$(methods.divMain).on("click", ".bam-multiregions-link", function () {
				methods._clickShowPopupForm(this);
				return false;
			});
			$(methods.divPopup).on("change", ".bam-multiregions-popup-content-search", function () {
				methods._changeSearchCity(this);
				return false;
			});
			$(methods.divPopup).on("keyup", ".bam-multiregions-popup-content-search", function () {
				methods._changeSearchCity(this);
				return false;
			});
			$(methods.divPopup).on("click", ".bam-multiregions-popup-content-item-link", function () {
				methods._selectCityFromPopup(this);
				return false;
			});
			$(methods.divPopup).on("click", ".bam-multiregions-popup-close", function () {
				methods._clickHidePopupForm(this);
				return false;
			});
			$(window).resize(function () {
				methods._doResizeContent();
			});
			return this;

		},
		destroy: function () {
			//$(this).off("change", "[data-sets-selector='Y']");
			return this;

		},
		_showConfirmCityWindow: function () {
			methods.divConfirm.fadeIn();
		},
		_clickConfirmButtonYes: function (element) {
			var sendData = {};
			sendData['PREVENT_CITY'] = methods.arParamsInfo['PREVENT_CITY'];
			sendData['sid'] = methods.arParamsInfo['SITE_ID'];
			sendData['AJAX'] = "Y";
			sendData['action'] = "setPreventCity";
			$.ajax(methods.urlAjax, {
				cache: false,
				context: $(element),
				data: sendData,
				dataType: "json",
				method: "POST",
				type: "POST",
				success: function (dataResult) {
					if (dataResult.STATUS == "SUCCESS") {
						methods.divConfirm.fadeOut(400, function () {
							methods.divConfirm.remove();
							methods.divConfirm = null;
						});
						if (dataResult.LOCATION != undefined && dataResult.LOCATION.length > 0) {
							window.location = dataResult.LOCATION;
						}
					}
				}
			});
		},
		_clickConfirmButtonNo: function (element) {
			methods.divConfirm.hide(1, function () {
				methods.divConfirm.remove();
				methods.divConfirm = null;
			});
			methods._clickShowPopupForm(element);
		},
		_clickShowPopupForm: function (element) {
			if (methods.divConfirm) {
				methods.divConfirm.hide(1, function () {
					methods.divConfirm.remove();
					methods.divConfirm = null;
				});
			}
			methods.doShowPopupForm();
		},
		_clickHidePopupForm: function (element) {
			methods.divPopup.hide();
			methods.divPopupBg.hide();
		},
		doShowPopupForm: function () {
			if (this.arParamsInfo['USE_GPS'] == "Y" && navigator.geolocation && methods.gpsPosition == false) {
				methods.gpsWait = true;
				methods.divPopupBg.fadeIn(400);
				navigator.geolocation.getCurrentPosition(methods.navigatorPosition, methods.navigatorError);
				var waitInterval = setInterval(function () {
					if (!methods.gpsWait) {
						clearInterval(waitInterval);
						methods._doShowPopupForm();
					}
				}, 20);
			} else {
				methods._doShowPopupForm();
			}
		},
		_doShowPopupForm: function () {
			methods.divPopup.html("");
			methods.divPopup.append($(methods.popupTemplate));
			var sendData = methods.arParamsInfo;
			sendData['GPS'] = methods.gpsPosition;
			sendData['AJAX'] = "Y";
			sendData['action'] = "getCityForm";
			$.ajax(methods.urlAjax, {
				cache: false,
				context: methods.divPopup,
				data: sendData,
				dataType: "json",
				method: "POST",
				type: "POST",
				success: function (dataResult) {
					if (dataResult.RESULT['IS_OK']) {
						methods.divPopup.find(".bam-multiregions-popup-title h3").html(dataResult.RESULT['TITLE']);
						methods.divPopup.find(".bam-multiregions-popup-content-search").attr("placeholder", dataResult.RESULT['INPUT_PLACEHOLDER']);
						methods._doShowListCity(dataResult.RESULT['ITEMS']);
					}
				}
			});
			methods.divPopupBg.fadeIn(400);
			methods.divPopup.fadeIn(400, function () {
				methods._doResizeContent();
			});
		},
		_changeSearchCity: function (element) {
			var sendData = methods.arParamsInfo;
			sendData['GPS'] = methods.gpsPosition;
			sendData['AJAX'] = "Y";
			sendData['action'] = "getCityForm";
			sendData['qcity'] = $(element).val();
			$.ajax(methods.urlAjax, {
				cache: true,
				context: methods.divPopup,
				data: sendData,
				dataType: "json",
				method: "POST",
				type: "POST",
				success: function (dataResult) {
					if (dataResult.RESULT['IS_OK']) {
						methods._doShowListCity(dataResult.RESULT['ITEMS']);
					}
				}
			});
		},
		navigatorPosition: function (position) {
			methods.gpsPosition = position;
			methods.gpsWait = false;
		},
		navigatorError: function () {
			methods.gpsPosition = false;
			methods.gpsWait = false;
		},
		_doShowListCity: function (arItems) {
			var listContent = methods.divPopup.find(".bam-multiregions-popup-content-list");
			var tmpTemplateCode = "";
			listContent.html("");
			$.each(arItems, function (k, item) {
				if (item['IS_CURRENT'] == "Y") {
					tmpTemplateCode = methods.popupListContentItemCurrentTemplate;
				} else {
					tmpTemplateCode = methods.popupListContentItemTemplate;
				}
				if (item.DOMAIN_NAME != undefined) {
					tmpTemplateCode = tmpTemplateCode.replace("#CITY_NAME#", item.DOMAIN_NAME);
					tmpTemplateCode = tmpTemplateCode.replace("#CITY_ID#", item.CITY_ID);
					tmpTemplateCode = tmpTemplateCode.replace("#CITY_TITLE#", item.FULL_NAME);
				} else {
					tmpTemplateCode = tmpTemplateCode.replace("#CITY_NAME#", item.CITY_NAME + " <span>(" + item.FULL_NAME_NO_CITY + ")</span>");
					tmpTemplateCode = tmpTemplateCode.replace("#CITY_ID#", item.CITY_ID);
					tmpTemplateCode = tmpTemplateCode.replace("#CITY_TITLE#", item.FULL_NAME_NO_CITY);
				}
				listContent.append($(tmpTemplateCode));
			});
		},
		_selectCityFromPopup: function (element) {
			var sendData = {};
			sendData['PREVENT_CITY'] = $(element).data("id");
			sendData['sid'] = methods.arParamsInfo['SITE_ID'];
			sendData['AJAX'] = "Y";
			sendData['action'] = "setPreventCity";
			$.ajax(methods.urlAjax, {
				cache: false,
				context: $(element),
				data: sendData,
				dataType: "json",
				method: "POST",
				type: "POST",
				success: function (dataResult) {
					if (dataResult.STATUS == "SUCCESS") {
						methods._clickHidePopupForm(element);
						if (dataResult.LOCATION != undefined && dataResult.LOCATION.length > 0) {
							window.location = dataResult.LOCATION;
						} else {
							window.location.reload();
						}
					}
				}
			});
		},
		_doResizeContent: function () {
			if (methods.divPopup.find(".bam-multiregions-popup-content-list").length > 0) {
				methods.divPopup.find(".bam-multiregions-popup-content-list").height(methods.divPopup.find(".bam-multiregions-popup-window").height() - 100);
			}
		},
		arResultInfo: {},
		arParamsInfo: {},
		self: null,
		divConfirm: null,
		divPopup: null,
		divPopupBg: null,
		urlAjax: "/bitrix/components/kit/multiregions.selector/ajax.php",
		popupTemplate: '<div class="bam-multiregions-popup-window"><div class="bam-multiregions-popup-title"><h3></h3><a href="javascript:void(0)" class="bam-multiregions-popup-close"></a></div><div class="bam-multiregions-popup-content"><input type="text" value="" class="bam-multiregions-popup-content-search" placeholder=""/><div class="bam-multiregions-popup-content-list"></div></div></div>',
		popupListContentItemTemplate: '<div class="bam-multiregions-popup-content-item"><a href="javascript:void(0)" class="bam-multiregions-popup-content-item-link" data-id="#CITY_ID#" title="#CITY_TITLE#">#CITY_NAME#</a></div>',
		popupListContentItemCurrentTemplate: '<div class="bam-multiregions-popup-content-item"><a href="javascript:void(0)" class="bam-multiregions-popup-content-item-link bam-multiregions-popup-content-item-link-current" data-id="#CITY_ID#" title="#CITY_TITLE#">#CITY_NAME#</a></div>',
		gpsWait: false,
		gpsPosition: false
	};

	$.fn.kitMultiRegions = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Not exists method ' + method);
		}
	};
})(jQuery);