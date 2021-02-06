<section class="section my-5">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mx-auto text-center">
                <h3 class="desc mt-5 text-capitalize">There are {{$ratings->count()}} ratings</h3>
            </div>

            <div 
                class="col-lg-12 d-flex justify-content-between pt-5"
                style="overflow-x: scroll; white-space: nowrap; position: relative;"
            >
                @foreach ($ratings as $rating)
                    <div class="col-lg-5">
                        <div class="card" style="white-space: normal !important;">
                            <div class="card-header">
                                <div class="card-title">
                                    <h5 class="title text-capitalize">{{$rating->user_name}}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                {{$rating->body}}
                                <br>
                                <br>
                                <h5 class="text-primary title">
                                    Rating: <u>{{$rating->rating}}</u> / 10
                                </h5>
                            </div>
                            <div class="card-footer d-flex justify-content-around">
                                <a href="{{route('discard_rating', $rating->id)}}">
                                    <button class="btn btn-outline-warning">Discard rating</button>
                                </a>
                                <a href="{{route('publish_rating', $rating->id)}}">
                                    <button class="btn btn-outline-success">Publish rating</button>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</section>