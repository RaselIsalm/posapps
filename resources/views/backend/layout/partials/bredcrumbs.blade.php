@php
    $pageTitle = preg_replace('/\s+/u', ' ', trim($title ?? 'Dashboard'));
    $pageSubtitle = preg_replace('/\s+/u', ' ', trim($subtitle ?? 'Welcome back! Let’s start from where you left.'));
@endphp
<div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6 col-12">
          <h2>{{ __($pageTitle) }}</h2>
          <p class="mb-0 text-title-gray">{{ __($pageSubtitle) }}</p>
        </div>
        <div class="col-sm-6 col-12">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
            {{ $slot }}
          </ol>
        </div>
      </div>
    </div>
  </div>
