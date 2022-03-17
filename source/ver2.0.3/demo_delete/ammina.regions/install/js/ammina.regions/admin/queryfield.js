(function ($) {

	var methods = {
		init: function () {
			methods.self = this;
			if ($(this).data("isinit") === "Y") {
				return;
			}
			$(this).data("isinit", "Y");
			$(this).attr("data-isinit", "Y");
			$(this).addClass("bamregionsadm-input-field");
			if ($(this).data("min-length") === undefined) {
				$(this).data("min-length", '0');
			}
			$(this).addClass("bamregionsadm-input-field");
			$(this).data("original", $(this).val());
			$(this).change(function () {
				if ($(this).val().length >= parseInt($(this).data("min-length"))) {
					methods._changeValueInField($(this));
				}
				return false;
			});
			$("body").click(function () {
				itemRegionPopup = $(".bamregionsadm-popup");
				if (itemRegionPopup.length > 0) {
					var inp = itemRegionPopup.parents(".bamregionsadm-area-item").find(".bamregionsadm-input-field");
					inp.val(inp.data("original"));
					itemRegionPopup.remove();
				}
			});
			$(this).click(function () {
				if ($(this).val().length >= parseInt($(this).data("min-length"))) {
					methods._changeValueInField($(this));
				}
				return false;
			});
			$(this).keyup(function (e) {
				if (e.keyCode === 27) {
					$(this).val($(this).data("original"));
					$(".bamregionsadm-popup").remove();
				} else {
					if ($(this).val().length >= parseInt($(this).data("min-length"))) {
						methods._changeValueInField($(this));
					}
				}
				return false;
			});
			$(this).parents("td").on("click", ".bamregionsadm-popup-variant", function () {
				methods._clickVariantInPopupForm(this);
				return false;
			});
			return this;
		},
		destroy: function () {
			$(this).data("isinit", "Y");
			return this;
		},
		_changeValueInField: function (element) {
			var mainArea = $(element).parents(".bamregionsadm-area-item");
			var popupArea = mainArea.find(".bamregionsadm-popup");
			if (popupArea.length <= 0) {
				$(".bamregionsadm-popup").remove();
				mainArea.append($('<div class="bamregionsadm-popup"></div>'));
				popupArea = mainArea.find(".bamregionsadm-popup");
			}
			var sendData = {};
			sendData['AJAX'] = "Y";
			sendData['action'] = $(element).data("action");
			sendData['q'] = $(element).val();
			sendData['min-length'] = $(element).data("min-length");
			sendData['cnt'] = $(element).data("cnt");
			$.ajax(methods.urlAjax, {
				cache: false,
				context: $(element),
				data: sendData,
				dataType: "json",
				method: "POST",
				success: function (dataResult) {
					if (dataResult.STATUS === "SUCCESS") {
						popupArea.html("");
						$.each(dataResult.ITEMS, function (k, item) {
							popupArea.append($('<div class="bamregionsadm-popup-variant" data-id="' + item.ID + '" data-text="' + item.FULL_NAME + '">' + item.FORMAT_NAME + '</div>'));
						});
					}
				}
			});
		},
		_clickVariantInPopupForm: function (element) {
			var identField = $(element).parents(".bamregionsadm-area-item").find(".bamregionsadm-input-field").data("result-id");
			$(element).parents(".bamregionsadm-area-item").find(".bamregionsadm-input-field").val($(element).data("text"));
			$(element).parents(".bamregionsadm-area-item").find(".bamregionsadm-input-field").data("original", $(element).data("text"));
			if ($(element).data("id") === '0') {
				$("#" + identField).val("");
			} else {
				$("#" + identField).val($(element).data("id"));
			}
			$(".bamregionsadm-popup").remove();
		},
		self: null,
		urlAjax: "/bitrix/admin/ammina.regions.ajax.php",
	};

	$.fn.amminaRegionsAdminQueryField = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Not exists method ' + method);
		}
	};
})(jQuery);


$(document).ready(function () {
	$(".amr-request-field").each(function () {
		$(this).amminaRegionsAdminQueryField();
	});
	window.setInterval(function () {
		var dialog = $(".bx-core-adm-admin-dialog");
		if (dialog.length > 0/* && dialog.data("isinitregions") !== "Y"*/) {
			var fields = dialog.find(".amr-request-field");
			if (fields.length > 0) {
				//dialog.data("isinitregions", "Y");
				fields.each(function () {
					$(this).amminaRegionsAdminQueryField();
				});
			}
		}
	}, 1000);
});
