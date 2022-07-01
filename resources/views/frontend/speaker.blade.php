@extends('frontend.layout.main')
@section('main-container')
<section class="speakers section-padding" id="section_3">
    <div class="container">
        <div class="row">

            <div class="col-lg-6 col-12 d-flex flex-column justify-content-center align-items-center">
                <div class="speakers-text-info">
                    <h2 class="mb-4">Our <u class="text-info">Speakers</u></h2>

                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut dolore</p>
                </div>
            </div>

            <div class="col-lg-6 col-12">
                <div class="speakers-thumb">
                    <img src="{{url('public/frontend/images/avatar/happy-asian-man-standing-with-arms-crossed-grey-wall.jpg')}}" class="img-fluid speakers-image" alt="">

                    <small class="speakers-featured-text">Featured</small>

                    <div class="speakers-info">

                        <h5 class="speakers-title mb-0">Logan Wilson</h5>

                        <p class="speakers-text mb-0">CEO / Founder</p>

                        <ul class="social-icon">
                            <li><a href="#" class="social-icon-link bi-facebook"></a></li>

                            <li><a href="#" class="social-icon-link bi-instagram"></a></li>

                            <li><a href="#" class="social-icon-link bi-google"></a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-12">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="speakers-thumb speakers-thumb-small">
                            <img src="{{url('public/frontend/images/avatar/portrait-good-looking-brunette-young-asian-woman.jpg')}}" class="img-fluid speakers-image" alt="">

                            <div class="speakers-info">
                                <h5 class="speakers-title mb-0">Natalie</h5>

                                <p class="speakers-text mb-0">Event Planner</p>

                                <ul class="social-icon">
                                    <li><a href="#" class="social-icon-link bi-facebook"></a></li>

                                    <li><a href="#" class="social-icon-link bi-instagram"></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="speakers-thumb speakers-thumb-small">
                            <img src="{{url('public/frontend/images/avatar/senior-man-white-sweater-eyeglasses.jpg')}}" class="img-fluid speakers-image" alt="">

                            <div class="speakers-info">
                                <h5 class="speakers-title mb-0">Thomas</h5>

                                <p class="speakers-text mb-0">Startup Coach</p>

                                <ul class="social-icon">
                                    <li><a href="#" class="social-icon-link bi-instagram"></a></li>

                                    <li><a href="#" class="social-icon-link bi-whatsapp"></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="speakers-thumb speakers-thumb-small">
                            <img src="{{url('public/frontend/images/avatar/pretty-smiling-joyfully-female-with-fair-hair-dressed-casually-looking-with-satisfaction.jpg')}}" class="img-fluid speakers-image" alt="">

                            <div class="speakers-info">
                                <h5 class="speakers-title mb-0">Isabella</h5>

                                <p class="speakers-text mb-0">Event Manager</p>

                                <ul class="social-icon">
                                    <li><a href="#" class="social-icon-link bi-facebook"></a></li>

                                    <li><a href="#" class="social-icon-link bi-instagram"></a></li>

                                    <li><a href="#" class="social-icon-link bi-whatsapp"></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="speakers-thumb speakers-thumb-small">
                            <img src="{{url('public/frontend/images/avatar/indoor-shot-beautiful-happy-african-american-woman-smiling-cheerfully-keeping-her-arms-folded-relaxing-indoors-after-morning-lectures-university.jpg')}}" class="img-fluid speakers-image" alt="">

                            <div class="speakers-info">
                                <h5 class="speakers-title mb-0">Samantha</h5>

                                <p class="speakers-text mb-0">Top Level Speaker</p>

                                <ul class="social-icon">
                                    <li><a href="#" class="social-icon-link bi-instagram"></a></li>

                                    <li><a href="#" class="social-icon-link bi-whatsapp"></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection