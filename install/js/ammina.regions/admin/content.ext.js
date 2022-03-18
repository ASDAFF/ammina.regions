$(document).ready(function () {
	$("#AR_DOMAIN_RULE_ADD").click(function () {
		var bodyTag = $(".ammina-regions-contentext-table tbody");
		var html = $(this).data("html");
		var newString = html.replace(new RegExp('NEXTNUM', 'g'), bodyTag.data("next"));
		bodyTag.append($(newString));
		bodyTag.data("next", parseInt(bodyTag.data("next")) + 1);
	});
	$(".ammina-regions-contentext-table tbody").on("change", ".ammina-regions-contentext-selecttype", function () {
		$(this).parents("tr:first").find(".ammina-regions-contentext-type").hide();
		if ($(this).val() == "TIMEFROMTO") {
			$(this).parents("tr:first").find(".ammina-regions-contentext-type-timefromto").show();
		} else if ($(this).val() == "WEEKDAYS") {
			$(this).parents("tr:first").find(".ammina-regions-contentext-type-weekdays").show();
		} else if ($(this).val() == "DATEFROMTO") {
			$(this).parents("tr:first").find(".ammina-regions-contentext-type-datefromto").show();
		} else if ($(this).val() == "PHPCONDITION") {
			$(this).parents("tr:first").find(".ammina-regions-contentext-type-phpcondition").show();
		}
	});
});