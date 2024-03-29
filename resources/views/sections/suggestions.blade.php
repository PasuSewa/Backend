<section class="section mt-5">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 mx-auto text-center">
                <h3 class="desc mt-5 text-capitalize">There are <u>{{$suggestions->count()}}</u> Suggestions</h3>
            </div>

            @if ($suggestions->count() === 0)
                <div 
                    class="col-lg-12 d-flex justify-content-between pt-5"
                >
            @else
                <div 
                    class="col-lg-12 d-flex justify-content-between pt-5"
                    style="overflow-x: scroll; white-space: nowrap; position: relative;"
                >
            @endif
                @foreach ($suggestions as $suggestion)
                    <div class="col-lg-5">
                        <div class="card" style="white-space: normal !important;">
                            <div class="card-header">
                                <div class="card-title">
                                    <h5 class="title text-capitalize">{{$suggestion->user_name}}</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                {{$suggestion->body}}
                            </div>
                            <div class="card-footer d-flex justify-content-around">
                                <a href="{{route('discard_feedback', $suggestion->id)}}">
                                    <button class="btn btn-outline-warning">Discard Suggestion</button>
                                </a>
                                @if (!$suggestion->is_public)
                                    <a href="{{route('publish_feedback', $suggestion->id)}}">
                                        <button class="btn btn-outline-success">Publish Suggestion</button>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</section>