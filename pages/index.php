<?php

// $subpage = rex_be_controller::getCurrentPagePart(2);

echo rex_view::title(rex_i18n::msg('consent_manager_title'));

// include rex_be_controller::getCurrentPageObject()->getSubPath();
rex_be_controller::includeCurrentPageSubPath();
