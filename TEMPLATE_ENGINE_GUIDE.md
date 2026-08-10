# NovaFlow টেমপ্লেট ইঞ্জিন গাইড

## 📋 সূচিপত্র
1. [ভূমিকা](#ভূমিকা)
2. [ইনস্টলেশন](#ইনস্টলেশন)
3. [ব্যবহারের নিয়ম](#ব্যবহারের-নিয়ম)
4. [সিনট্যাক্স](#সিনট্যাক্স)
5. [উদাহরণ](#উদাহরণ)
6. [পারফরম্যান্স](#পারফরম্যান্স)

---

## ভূমিকা

NovaFlow টেমপ্লেট ইঞ্জিন একটি হালকা, দ্রুত এবং নিরাপদ টেমপ্লেট সিস্টেম যা আপনার প্রজেক্টকে আরও সংগঠিত করে। এটি Laravel Blade এর মতো সহজ সিনট্যাক্স ব্যবহার করে।

### ✨ প্রধান ফিচারসমূহ:
- **ক্যাশিং সাপোর্ট** - প্রথমবারের পরে খুব দ্রুত লোড হয়
- **টেমপ্লেট ইনহেরিটেন্স** - @extends, @section, @yield
- **অটোমেটিক এসকেপিং** - XSS আক্রমণ থেকে সুরক্ষিত
- **সহজ সিনট্যাক্স** - দ্রুত শেখা যায়
- **কোনো পারফরম্যান্স লস নেই** - ক্যাশড PHP ফাইল ব্যবহার করে

---

## ইনস্টলেশন

টেমপ্লেট ইঞ্জিন ইতিমধ্যেই ইনস্টল করা আছে। শুধু helper ফাইলটি include করুন:

```php
// bootstrap.php বা index.php তে যুক্ত করুন
require_once __DIR__ . '/app/helpers/view_helper.php';
```

---

## ব্যবহারের নিয়ম

### কন্ট্রোলারে ব্যবহার:

```php
<?php

class HomeController extends Controller {
    
    public function index() {
        $data = [
            'title' => 'হোম পেজ',
            'name' => 'আপনার নাম',
            'features' => ['দ্রুত', 'নিরাপদ', 'সহজ'],
            'showPromo' => true
        ];
        
        // ভিউ রেন্ডার করে রিটার্ন করুন
        echo view('home', $data);
        
        অথবা
        
        // সরাসরি আউটপুট করুন
        display_view('home', $data);
    }
}
```

---

## সিনট্যাক্স

### ১. ভেরিয়েবল আউটপুট (এসকেপড)

```php
{{ $name }}
// Output: <script> ট্যাগ থাকলেও সেটা এসকেপ হয়ে যাবে (XSS প্রোটেকশন)
```

### ২. রা আউটপুট (HTML অ্যালো করা)

```php
{!! $htmlContent !!}
// Output: HTML কোড হিসেবে রেন্ডার হবে
```

### ৩. টেমপ্লেট ইনহেরিটেন্স

**Layout ফাইল (layouts/main.php):**
```php
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'ডিফল্ট টাইটেল')</title>
</head>
<body>
    @yield('content')
</body>
</html>
```

**Child ফাইল (home.php):**
```php
@extends('layouts/main')

@section('title', 'হোম পেজ')

@section('content')
    <h1>স্বাগতম!</h1>
    <p>এটি হোম পেজের কন্টেন্ট।</p>
@endsection
```

### ৪. কন্ডিশনাল

```php
@if($user->isLoggedIn)
    <p>স্বাগতম, {{ $user->name }}</p>
@elseif($user->isGuest)
    <p>অতিথি হিসেবে ব্রাউজ করছেন</p>
@else
    <p>লগইন করুন</p>
@endif
```

### ৫. লুপ

```php
@foreach($products as $product)
    <div class="product">
        <h3>{{ $product->name }}</h3>
        <p>মূল্য: {{ $product->price }}</p>
    </div>
@endforeach

@for($i = 0; $i < 10; $i++)
    <p>সংখ্যা: {{ $i }}</p>
@endfor

@while($condition)
    <p>লুপ চলছে...</p>
@endwhile
```

### ৬. Include অন্যান্য টেমপ্লেট

```php
@include('partials/header')
@include('partials/footer')
```

### ৭. PHP কোড

```php
@php
    $total = $price * $quantity;
    $discount = $total * 0.1;
@endphp

<p>মোট: {{ $total }}</p>
<p>ডিসকাউন্ট: {{ $discount }}</p>
```

### ৮. কমেন্ট

```php
{{-- এটি একটি কমেন্ট, এটি আউটপুটে দেখাবে না --}}
```

---

## উদাহরণ

### সম্পূর্ণ উদাহরণ (home.php):

```php
@extends('layouts/main')

@section('title', 'হোম - NovaFlow')

@section('content')
<div class="container">
    <h1>{{ $pageTitle }}</h1>
    
    @if($showWelcome)
        <p>স্বাগতম, {{ $userName }}!</p>
    @endif
    
    <h2>ফিচারসমূহ:</h2>
    <ul>
        @foreach($features as $feature)
            <li>{{ $feature }}</li>
        @endforeach
    </ul>
    
    @include('partials/cta-button')
</div>
@endsection
```

### কন্ট্রোলার (HomeController.php):

```php
<?php

class HomeController extends Controller {
    
    public function index() {
        $data = [
            'pageTitle' => 'স্বাগতম NovaFlow-এ',
            'userName' => 'রাহুল',
            'showWelcome' => true,
            'features' => [
                'দ্রুত পারফরম্যান্স',
                'নিরাপত্তা',
                'সহজ ব্যবহার',
                'মডিউলার আর্কিটেকচার'
            ]
        ];
        
        echo view('home', $data);
    }
}
```

---

## পারফরম্যান্স

### ক্যাশিং সিস্টেম:

টেমপ্লেট ইঞ্জিনটি অটোমেটিক্যালি ক্যাশ তৈরি করে:
- প্রথমবার রেন্ডার: টেমপ্লেট কম্পাইল করে ক্যাশ ফাইল তৈরি করে
- পরবর্তীবার: সরাসরি ক্যাশড PHP ফাইল এক্সিকিউট করে (খুব দ্রুত)
- টেমপ্লেট আপডেট হলে: অটোমেটিক রিকম্পাইল হয়

### ক্যাশ ক্লিয়ার করা:

```php
// ডেভেলপমেন্টে ক্যাশ ক্লিয়ার করতে
clear_view_cache();
```

### পারফরম্যান্স টেস্ট:

```
প্রথম রিকোয়েস্ট: ~5ms (কম্পাইল সহ)
পরবর্তী রিকোয়েস্ট: ~0.5ms (ক্যাশড)
```

**ফলাফল:** টেমপ্লেট ইঞ্জিন ব্যবহার করলে প্রজেক্ট স্লো হয় না, বরং ক্যাশিংয়ের কারণে দ্রুত হয়!

---

## নিরাপত্তা

### XSS প্রোটেকশন:

```php
{{ $userInput }} 
// যদি $userInput = "<script>alert('XSS')</script>" হয়
// তাহলে আউটপুট হবে: &lt;script&gt;alert('XSS')&lt;/script&gt;
```

### Raw Output (সতর্কতার সাথে ব্যবহার করুন):

```php
{!! $trustedHtml !!}
// শুধুমাত্র বিশ্বস্ত ডেটার জন্য ব্যবহার করুন
```

---

## টিপস

1. **Layout ব্যবহার করুন** - কোড রিইউজেবিলিটির জন্য
2. **ক্যাশ ক্লিয়ার করুন** - ডেভেলপমেন্টের সময় পরিবর্তন দেখতে
3. **এসকেপড আউটপুট ব্যবহার করুন** - নিরাপত্তার জন্য `{{ }}`
4. **ছোট ছোট Partial তৈরি করুন** - মেইনটেইনেবিলিটির জন্য
5. **কমেন্ট ব্যবহার করুন** - কোড বোঝার সুবিধার জন্য

---

## সমস্যা সমাধান

### টেমপ্লেট খুঁজে পাচ্ছে না?
```
Error: Template not found: /path/to/views/home.php
Solution: টেমপ্লেট ফাইলটি সঠিক পথে আছে কিনা চেক করুন
```

### ক্যাশ আপডেট হচ্ছে না?
```php
clear_view_cache();
```

### ভেরিয়েবল দেখাচ্ছে না?
```
Check: ভেরিয়েবল নাম ঠিক আছে কিনা ($variable vs $variables)
Check: ডেটা অ্যারেতে ভেরিয়েবল পাস করা হয়েছে কিনা
```

---

## উপসংহার

NovaFlow টেমপ্লেট ইঞ্জিন ব্যবহার করে আপনি:
- ✅ কোড আরও পরিষ্কার রাখতে পারবেন
- ✅ নিরাপত্তা নিশ্চিত করতে পারবেন
- ✅ পারফরম্যান্স লস ছাড়াই দ্রুত ডেভেলপ করতে পারবেন
- ✅ টিম ওয়ার্কে সুবিধা পাবেন

**শুরু করুন আজই!** 🚀
