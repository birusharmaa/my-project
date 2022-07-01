@extends('frontend.layout.main')
@section('main-container')
<section class="about section-padding" id="section_2">
    <div class="container">
        <div class="row">

            <div class="col-lg-10 col-12">
                <h2 class="mb-4">Our <u class="text-info">Story</u></h2>
            </div>

            <div class="col-lg-6 col-12">
                <h3 class="mb-3">The importance of Leadership Conference in 2022</h3>

                <p>Leadership Event is one-page Bootstrap v5.1.3 CSS layout for your website. Thank you for choosing TemplateMo website where you can instantly download free CSS templates at no cost.</p>

                <a class="custom-btn custom-border-btn btn custom-link mt-3 me-3" href="#section_3">Meet Speakers</a>

                <a class="custom-btn btn custom-link mt-3" href="#section_4">Check out Schedule</a>
            </div>

            <div class="col-lg-6 col-12 mt-5 mt-lg-0">
                <h4>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut dolore</h4>

                <div class="avatar-group border-top py-5 mt-5">
                    <img src="{{url('public/frontend/images/avatar/portrait-good-looking-brunette-young-asian-woman.jpg')}}" class="img-fluid avatar-image" alt="">

                    <img src="{{url('public/frontend/images/avatar/happy-asian-man-standing-with-arms-crossed-grey-wall.jpg')}}" class="img-fluid avatar-image avatar-image-left" alt="">

                    <img src="{{url('public/frontend/images/avatar/senior-man-white-sweater-eyeglasses.jpg')}}" class="img-fluid avatar-image avatar-image-left" alt="">

                    <img src="{{url('public/frontend/images/avatar/pretty-smiling-joyfully-female-with-fair-hair-dressed-casually-looking-with-satisfaction.jpg')}}" class="img-fluid avatar-image avatar-image-left" alt="">

                    <p class="d-inline">120+ People are attending with us</p>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection