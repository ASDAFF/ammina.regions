$(document).ready(function () {
	$("#AR_DOMAIN_LOCATION_ADD").click(function () {
		var bodyTag = $(".ammina-regions-domain-location-table tbody");
		var nNum = bodyTag.data("next");
		var newStr = '<tr><td><div class="bamregionsadm-area-item"><input type="text" class="adm-bus-input" maxlength="255" id="FIELD_LOCATION_COUNTRY_n' + bodyTag.data("next") + '" value="" data-action="country" data-result-id="FIELD_LOCATION_COUNTRY_ID_n' + bodyTag.data("next") + '" autocomplete="off" /><input type="hidden" name="FIELDS[LOCATION][n' + bodyTag.data("next") + '][COUNTRY_ID]" id="FIELD_LOCATION_COUNTRY_ID_n' + bodyTag.data("next") + '" value="" /></div></td><td><div class="bamregionsadm-area-item"><input type="text" class="adm-bus-input" maxlength="255" id="FIELD_LOCATION_REGION_n' + bodyTag.data("next") + '" value="" data-action="region" data-result-id="FIELD_LOCATION_REGION_ID_n' + bodyTag.data("next") + '" autocomplete="off" /><input type="hidden" name="FIELDS[LOCATION][n' + bodyTag.data("next") + '][REGION_ID]" id="FIELD_LOCATION_REGION_ID_n' + bodyTag.data("next") + '" value="" /></div></td><td><div class="bamregionsadm-area-item"><input type="text" class="adm-bus-input" maxlength="255" id="FIELD_LOCATION_CITY_n' + bodyTag.data("next") + '" value="" data-action="city" data-result-id="FIELD_LOCATION_CITY_ID_n' + bodyTag.data("next") + '" autocomplete="off" /><input type="hidden" name="FIELDS[LOCATION][n' + bodyTag.data("next") + '][CITY_ID]" id="FIELD_LOCATION_CITY_ID_n' + bodyTag.data("next") + '" value="" /></div></td><td style="text-align:center;"><input type="checkbox" name="FIELDS[LOCATION][n' + bodyTag.data("next") + '][DELETE]" value="Y" class="adm-designed-checkbox" id="FIELD_LOCATION_DELETE_n' + bodyTag.data("next") + '" /><label for="FIELD_LOCATION_DELETE_n' + bodyTag.data("next") + '" class="adm-designed-checkbox-label" title="" /></td></tr>';
		bodyTag.append($(newStr));
		bodyTag.data("next", parseInt(bodyTag.data("next")) + 1);
		$("#FIELD_LOCATION_COUNTRY_n" + nNum).amminaRegionsAdminBlockContent();
		$("#FIELD_LOCATION_REGION_n" + nNum).amminaRegionsAdminBlockContent();
		$("#FIELD_LOCATION_CITY_n" + nNum).amminaRegionsAdminBlockContent();
	});
});