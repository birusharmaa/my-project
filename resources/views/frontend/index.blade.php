@extends('frontend.layout.main')
@section('main-container')

<section class="hero" id="section_1">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 col-12 m-auto">
                <div class="hero-text">

                    <h1 class="text-white mb-4"><u class="text-info">Leadership</u> Conference 2022</h1>

                    <div class="d-flex justify-content-center align-items-center">
                        <span class="date-text">July 12 to 18, 2022</span>

                        <span class="location-text">Times Square, NY</span>
                    </div>

                    <a href="#section_2" class="custom-link bi-arrow-down arrow-icon"></a>
                </div>
            </div>
        </div>
    </div>

    <div class="video-wrap">
        <video autoplay="" loop="" muted="" class="custom-video" poster="">
            <source src="videos/pexels-pavel-danilyuk-8716790.mp4" type="video/mp4">

            Your browser does not support the video tag.
        </video>
    </div>
</section>


<section class="highlight">
    <div class="container">
        <div class="row">

            <div class="col-lg-4 col-md-6 col-12">
                <div class="highlight-thumb">
                    <img src="{{url('public/frontend/images/highlight/alexandre-pellaes-6vAjp0pscX0-unsplash.jpg')}}" class="highlight-image img-fluid" alt="">

                    <div class="highlight-info">
                        <h3 class="highlight-title">2019 Highlights</h3>

                        <a href="https://www.youtube.com/templatemo" class="bi-youtube highlight-icon"></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12">
                <div class="highlight-thumb">
                    <img src="{{url('public/frontend/images/highlight/miguel-henriques--8atMWER8bI-unsplash.jpg')}}" class="highlight-image img-fluid" alt="">

                    <div class="highlight-info">
                        <h3 class="highlight-title">2020 Highlights</h3>

                        <a href="https://www.youtube.com/templatemo" class="bi-youtube highlight-icon"></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12">
                <div class="highlight-thumb">
                    <img src="{{url('public/frontend/images/highlight/jakob-dalbjorn-cuKJre3nyYc-unsplash.jpg')}}" class="highlight-image img-fluid" alt="">

                    <div class="highlight-info">
                        <h3 class="highlight-title">2021 Highlights</h3>

                        <a href="https://www.youtube.com/templatemo" class="bi-youtube highlight-icon"></a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@include('frontend.about')
@include('frontend.speaker')
@include('frontend.schedule')
@include('frontend.pricing')
@include('frontend.venue')
@include('frontend.contact')            
@endsection

        