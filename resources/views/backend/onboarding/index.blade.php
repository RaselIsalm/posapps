@extends('backend.layout.main')

@section('title', 'Onboarding & Update News')

@push('css')
<style>
    .onboarding-hero {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color) 100%);
        border-radius: 20px;
        color: #fff;
        padding: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
    }
    .onboarding-hero::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    .onboarding-hero::before {
        content: '';
        position: absolute;
        bottom: -80px;
        right: 100px;
        width: 250px;
        height: 250px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }
    .announcement-card {
        background: #fff;
        border-left: 5px solid #f59e0b;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        padding: 20px 25px;
        position: relative;
    }
    .announcement-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .resource-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.03);
        padding: 30px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .resource-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        border-color: rgba(0,0,0,0.08);
    }
    .icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 20px;
    }
    .icon-video {
        background: rgba(14, 165, 233, 0.1);
        color: #0ea5e9;
    }
    .icon-docs {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }
    .card-btn {
        margin-top: auto;
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.3s;
    }
    .card-btn-video {
        background: #f0f9ff;
        color: #0ea5e9;
        border: none;
    }
    .card-btn-video:hover {
        background: #0ea5e9;
        color: #fff;
    }
    .card-btn-docs {
        background: #f0fdf4;
        color: #22c55e;
        border: none;
    }
    .card-btn-docs:hover {
        background: #22c55e;
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    
    <!-- Header Section -->
    <div class="d-flex align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="fas fa-rocket text-primary me-2"></i> Onboarding & Updates
        </h3>
    </div>

    <!-- Announcement Section -->
    @if(!empty($announcement))
    <div class="announcement-card mb-4">
        <div class="d-flex align-items-start">
            <div class="announcement-icon flex-shrink-0 me-3">
                <i class="fas fa-bell"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-2">System Announcement</h5>
                <div class="text-muted" style="white-space: pre-line; line-height: 1.6; font-size: 0.95rem;">
                    {{ $announcement }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Hero Welcome Banner -->
    <div class="onboarding-hero mb-5">
        <div class="row align-items-center">
            <div class="col-lg-8 position-relative" style="z-index: 2;">
                <h2 class="fw-bold mb-3">Welcome to OMS OneTech! 👋</h2>
                <p class="mb-0 fs-5" style="color: rgba(255,255,255,0.9);">
                    Here you will find the latest updates, video tutorials, and getting-started guides to master your POS and Order Management System.
                </p>
                <div class="mt-4">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm">
                        <i class="fas fa-sparkles me-1"></i> Stay tuned for new features!
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Resources Section -->
    <div class="row g-4">
        <div class="col-xl-6 col-lg-6">
            <div class="resource-card">
                <div class="icon-wrapper icon-video">
                    <i class="fas fa-play"></i>
                </div>
                <h4 class="fw-bold text-dark mb-3">Video Tutorials</h4>
                <p class="text-muted mb-4" style="font-size: 1.05rem; line-height: 1.6;">
                    Learn how to set up your products, manage stock, and process sales quickly with our comprehensive, easy-to-follow video guides.
                </p>
                <a href="{{ $videoLink }}" target="_blank" class="btn card-btn card-btn-video align-self-start">
                    <i class="fas fa-external-link-alt me-2"></i>Watch Videos
                </a>
            </div>
        </div>
        
        <div class="col-xl-6 col-lg-6">
            <div class="resource-card">
                <div class="icon-wrapper icon-docs">
                    <i class="fas fa-book-open"></i>
                </div>
                <h4 class="fw-bold text-dark mb-3">Documentation</h4>
                <p class="text-muted mb-4" style="font-size: 1.05rem; line-height: 1.6;">
                    Read step-by-step instructions on configuring your system settings, adding employees, managing roles, and generating detailed reports.
                </p>
                <a href="{{ $docsLink }}" target="_blank" class="btn card-btn card-btn-docs align-self-start">
                    <i class="fas fa-file-alt me-2"></i>Read Docs
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
