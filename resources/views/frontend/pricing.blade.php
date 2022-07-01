@extends('frontend.layout.main')
@section('main-container')
<section class="call-to-action section-padding">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-7 col-12">
                <h2 class="text-white mb-4">Become an <u class="text-info">event speaker?</u></h2>

                <p class="text-white">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut dolore</p>
            </div>

            <div class="col-lg-3 col-12 ms-lg-auto mt-4 mt-lg-0">
                <a href="#section_5" class="custom-btn btn">Register Today</a>
            </div>

        </div>
    </div>
</section>

 <section class="pricing section-padding" id="section_5">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-12 text-center mx-auto mb-5">
                <h2>Get Your <u class="text-info">Tickets</u></h2>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mb-5 mb-lg-0">
                <div class="pricing-thumb bg-white shadow-lg">                                
                    <div class="pricing-title-wrap d-flex align-items-center">
                        <h4 class="pricing-title text-white mb-0">Early Bird</h4>
                        <h5 class="pricing-small-title text-white mb-0 ms-auto">$640</h5>
                    </div>
                    <div class="pricing-body">
                        <p>
                            <i class="bi-cup me-2"></i> All-Day Coffee + Snacks
                        </p>
                        <p>
                            <i class="bi-controller me-2"></i> After Party
                        </p>
                        <p>
                            <i class="bi-chat-square me-2"></i> 24/7 Support
                        </p>
                        <div class="border-bottom pb-3 mb-4"></div>
                        <p>Quick group meetings for multiple teams</p>
                        <a class="custom-btn btn mt-3" href="#">Buy Tickets</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mb-5 mb-lg-0">
                <div class="pricing-thumb bg-white shadow-lg">                                
                    <div class="pricing-title-wrap d-flex align-items-center">
                        <h4 class="pricing-title text-white mb-0">Gold</h4>
                        <h5 class="pricing-small-title text-white mb-0 ms-auto">$840</h5>
                    </div>
                    <div class="pricing-body">
                        <p>
                            <i class="bi-cup me-2"></i> All-Day Coffee + Snacks
                        </p>
                        <p>
                            <i class="bi-boombox me-2"></i> Group Meetings + After Party
                        </p>
                        <p>
                            <i class="bi-chat-square me-2"></i> 24/7 Support + Instant Chats
                        </p>
                        <div class="border-bottom pb-3 mb-4"></div>
                        <p>Quick group meetings for multiple teams</p>
                        <a class="custom-btn btn mt-3" href="#">Buy Tickets</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="pricing-thumb bg-white shadow-lg">                                
                    <div class="pricing-title-wrap d-flex align-items-center">
                        <h4 class="pricing-title text-white mb-0">Platinum</h4>
                        <h5 class="pricing-small-title text-white mb-0 ms-auto">$1,240</h5>
                    </div>
                    <div class="pricing-body">
                        <p>
                            <i class="bi-cash me-2"></i> Cashback $200
                        </p>
                        <p>
                            <i class="bi-boombox me-2"></i> Private Meetings + After Party
                        </p>
                        <p>
                            <i class="bi-chat-square me-2"></i> 24/7 Support + Instant Chats
                        </p>
                        <div class="border-bottom pb-3 mb-4"></div>
                        <p>group talks and private chats for multiple teams</p>
                        <a class="custom-btn btn mt-3" href="#">Buy Tickets</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection