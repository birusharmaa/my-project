@extends('frontend.layout.main')
@section('main-container')
<section class="contact section-padding" id="section_7">
    <div class="container">
        <div class="row">

            <div class="col-lg-8 col-12 mx-auto">
                <form class="custom-form contact-form bg-white shadow-lg" action="#" method="post" role="form">
                    <h2>Please Say Hi</h2>

                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-12">                                    
                            <input type="text" name="name" id="name" class="form-control" placeholder="Name" required="">
                        </div>

                        <div class="col-lg-4 col-md-4 col-12">         
                            <input type="email" name="email" id="email" pattern="[^ @]*@[^ @]*" class="form-control" placeholder="Email" required="">
                        </div>

                        <div class="col-lg-4 col-md-4 col-12">                                    
                            <input type="text" name="subject" id="subject" class="form-control" placeholder="Subject">
                        </div>

                        <div class="col-12">
                            <textarea class="form-control" rows="5" id="message" name="message" placeholder="Message"></textarea>

                            <button type="submit" class="form-control">Submit</button>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>
</section>
@endsection