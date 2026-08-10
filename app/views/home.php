@extends('layouts/main')

@section('title', 'হোম - NovaFlow')

@section('content')
<div class="container py-5">
    <div class="row align-items-center min-vh-75">
        <div class="col-lg-6">
            <h1 class="display-3 fw-bold mb-4">
                স্বাগতম <span class="text-danger">NovaFlow</span>-এ
            </h1>
            <p class="lead text-muted mb-4">
                একটি আধুনিক, দ্রুত এবং নিরাপদ PHP MVC ফ্রেমওয়ার্ক যা প্রফেশনাল ডেভেলপারদের জন্য তৈরি।
            </p>
            <div class="d-flex gap-3">
                <a href="<?= BASE_URL ?>/docs" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold">শুরু করুন</a>
                <a href="https://github.com/yourusername/novafLOW" class="btn btn-outline-dark btn-lg rounded-pill px-4">
                    <i class="fab fa-github me-2"></i> GitHub
                </a>
            </div>
            
            <div class="mt-5">
                <h5 class="fw-semibold mb-3">✨ নতুন ফিচারসমূহ:</h5>
                <ul class="list-unstyled">
                    @foreach($features as $feature)
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <div class="col-lg-6 text-center">
            <img src="https://via.placeholder.com/500x400?text=NovaFlow+Framework" 
                 alt="NovaFlow Framework" 
                 class="img-fluid rounded-3 shadow-lg">
        </div>
    </div>
    
    <hr class="my-5">
    
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="fas fa-bolt fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold">দ্রুত পারফরম্যান্স</h5>
                    <p class="text-muted">অপ্টিমাইজড কোডবেস এবং ক্যাশিং সিস্টেমের মাধ্যমে দ্রুত লোডিং।</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="fas fa-shield-alt fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold">নিরাপত্তা</h5>
                    <p class="text-muted">XSS, SQL Injection এবং CSRF থেকে সুরক্ষিত অটোমেটিক প্রোটেকশন।</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="fas fa-code fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold">সহজ ব্যবহার</h5>
                    <p class="text-muted">সহজ এবং পরিষ্কার সিনট্যাক্স যা দ্রুত শেখা যায়।</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if($showPromo)
<div class="bg-light py-5 mt-5">
    <div class="container text-center">
        <h3 class="fw-bold mb-3">🎉 বিশেষ অফার!</h3>
        <p class="lead">NovaFlow Pro ভার্সন এখন ৫০% ছাড়ে!</p>
        <a href="#" class="btn btn-danger btn-lg rounded-pill px-5">এখনই কিনুন</a>
    </div>
</div>
@endif
@endsection
