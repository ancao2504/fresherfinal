/**
 * CPEventManagement EditView JS
 * Author: Phu Vo
 * Date: 2020.05.23
 */

$(function() {
    if ($(':input[name="location"]')[0]) GoogleMaps.initAutocomplete($(':input[name="location"]'));
});