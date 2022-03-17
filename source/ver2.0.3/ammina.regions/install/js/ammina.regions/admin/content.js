(function ($) {

	var methods = {
		init: function () {
			methods.self = this;
			$(this).addClass("bamregionsadm-input-field");
			$(this).data("original", $(this).val());
			$(this).change(function () {
				methods._changeValueInField($(this));
				return false;
			});
			$("body").click(function () {
				if ($(".bamregionsadm-popup").length > 0) {
					var inp = $(".bamregionsadm-popup").parents(".bamregionsadm-area-item").find(".bamregionsadm-input-field");
					inp.val(inp.data("original"));
					$(".bamregionsadm-popup").remove();
				}
			});
			$(this).keyup(function (e) {
				if (e.keyCode == 27) {
					$(this).val($(this).data("original"));
					$(".bamregionsadm-popup").remove();
				} else {
					methods._changeValueInField($(this));
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
			$.ajax(methods.urlAjax, {
				cache: false,
				context: $(element),
				data: sendData,
				dataType: "json",
				method: "POST",
				success: function (dataResult) {
					if (dataResult.STATUS == "SUCCESS") {
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
			$("#" + identField).val($(element).data("id"));
			$(".bamregionsadm-popup").remove();
		},
		self: null,
		urlAjax: "/bitrix/admin/ammina.regions.ajax.php",
	};

	$.fn.amminaRegionsAdminBlockContent = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Not exists method ' + method);
		}
	};
})(jQuery);