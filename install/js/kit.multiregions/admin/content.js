(function ($) {

	var methods = {
		init: function () {
			methods.self = this;
			$(this).addClass("bammultiregionsadm-input-field");
			$(this).data("original", $(this).val());
			$(this).change(function () {
				methods._changeValueInField($(this));
				return false;
			});
			$("body").click(function () {
				if ($(".bammultiregionsadm-popup").length > 0) {
					var inp = $(".bammultiregionsadm-popup").parents(".bammultiregionsadm-area-item").find(".bammultiregionsadm-input-field");
					inp.val(inp.data("original"));
					$(".bammultiregionsadm-popup").remove();
				}
			});
			$(this).keyup(function (e) {
				if (e.keyCode == 27) {
					$(this).val($(this).data("original"));
					$(".bammultiregionsadm-popup").remove();
				} else {
					methods._changeValueInField($(this));
				}
				return false;
			});
			$(this).parents("td").on("click", ".bammultiregionsadm-popup-variant", function () {
				methods._clickVariantInPopupForm(this);
				return false;
			});
			return this;

		},
		destroy: function () {
			return this;

		},
		_changeValueInField: function (element) {
			var mainArea = $(element).parents(".bammultiregionsadm-area-item");
			var popupArea = mainArea.find(".bammultiregionsadm-popup");
			if (popupArea.length <= 0) {
				$(".bammultiregionsadm-popup").remove();
				mainArea.append($('<div class="bammultiregionsadm-popup"></div>'));
				popupArea = mainArea.find(".bammultiregionsadm-popup");
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
							popupArea.append($('<div class="bammultiregionsadm-popup-variant" data-id="' + item.ID + '" data-text="' + item.FULL_NAME + '">' + item.FORMAT_NAME + '</div>'));
						});
					}
				}
			});
		},
		_clickVariantInPopupForm: function (element) {
			var identField = $(element).parents(".bammultiregionsadm-area-item").find(".bammultiregionsadm-input-field").data("result-id");
			$(element).parents(".bammultiregionsadm-area-item").find(".bammultiregionsadm-input-field").val($(element).data("text"));
			$(element).parents(".bammultiregionsadm-area-item").find(".bammultiregionsadm-input-field").data("original", $(element).data("text"));
			$("#" + identField).val($(element).data("id"));
			$(".bammultiregionsadm-popup").remove();
		},
		self: null,
		urlAjax: "/bitrix/admin/kit.multiregions.ajax.php",
	};

	$.fn.kitMultiRegionsAdminBlockContent = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Not exists method ' + method);
		}
	};
})(jQuery);