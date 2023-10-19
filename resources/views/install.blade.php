<div>
    <div>
        <form action="{{route('install.save')}}" method="post">
            @csrf
            <label for="website_id">
                WebsiteId
                <input type="text" id="website_id" name="website_id" max="128">
            </label>
            <input type="hidden" value="{{$shop}}" name="shop_external_id">
            <br>
            <label for="substitute_product">
                Podmieniać produkt?
                <input type="checkbox" id="substitute_product" name="substitute_product">
            </label>
            <input type="submit">
            @if($errors->any())
                <div style="background-color: red;">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('success'))
                <div style="background-color: green;">
                    Wszystko okej, zapisano zmiany.
                </div>
            @endif
        </form>
    </div>
</div>
