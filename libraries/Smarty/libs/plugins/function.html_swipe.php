<?php
    // Name: function html_swipe
    // Author: Phu Vo
    // Date: 2019.07.02
    // Description: To Simple Generate JQuery Swipe
    // Requirement: Jquery, JquerySwipe (js, css, init, icons, default image)

    // Input: value, title, group

    // Link :
    // JQuerySwipeJs: resources/libraries/SwipeBox/jquery.swipebox.min.js
    // JQuerySwipeInit: resources/libraries/SwipeBox/swipe.init.js
    // JQuerySwipeCss: resources/libraries/SwipeBox/swipebox.css
    // JQuerySwipeIcons: resources/libraries/SwipeBox/
    // JQuerySwipeIcons: resources/images/noimage.jpg

    function smarty_function_html_swipe ($props) {
        $html = "";
        
        $value = $props['value'];
        $title = $props['title'];
        $group = $props['group'] ? $props['group'] : '1';

        $link = ($value == '' || $value == null) ?
            "resources/images/noimage.jpg" :
            "$value"
        ;

        $html .= "<div class='swipe-image-holder-sm'>";
        $html .= "<a href='{$link}' class='swipebox animate-bg-zoomout' title='{$title}' style='background-image: url({$link})'></a>";
        $html .= "<div>";

        return $html;
    }
?>