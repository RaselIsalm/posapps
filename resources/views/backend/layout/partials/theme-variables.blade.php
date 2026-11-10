<style>
:root {
    --theme-default: {{ \App\Helpers\ThemeHelper::getPrimaryColor() }};
    --primary-color: {{ \App\Helpers\ThemeHelper::getPrimaryColor() }};
    --primary-hover: {{ \App\Helpers\ThemeHelper::getHoverColor() }};
    --primary-light: {{ \App\Helpers\ThemeHelper::getLightColor() }};
    --primary-rgb: {{ \App\Helpers\ThemeHelper::getRgb() }};
    --link-color: {{ \App\Helpers\ThemeHelper::getLinkColor() }};
    --toast-success-color: {{ \App\Helpers\ThemeHelper::getToastSuccessColor() }};
    --bs-primary: {{ \App\Helpers\ThemeHelper::getPrimaryColor() }};
    --bs-primary-rgb: {{ \App\Helpers\ThemeHelper::getRgb() }};
    --bs-link-color: {{ \App\Helpers\ThemeHelper::getLinkColor() }};
    --bs-link-hover-color: {{ \App\Helpers\ThemeHelper::getHoverColor() }};
}
</style>
