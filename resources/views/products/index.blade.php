@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{ URL::to('/product/filter') }}" method="post" class="card-header">
            @csrf
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control"
                     value="{{ Request::get('title') ?? ''}}">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="variant" class="form-control">
                        <option value="">-- Select a Variant --</option>
                        @if(!empty($variantList))
                            @foreach($variantList as $varId => $var)
                                <option value="" readonly>{!! $var['title'] ?? '' !!}</option>
                                @if(!empty($var['var']))
                                    @foreach($var['var'] as $pVar => $pVar)
                                        <option value="{{$pVar}}" {{ !empty(Request::get('variant')) && Request::get('variant') == $pVar ? 'selected' : ''  }}>
                                            {!! !empty($pVar) ? '&nbsp;&nbsp;' . $pVar : '' !!}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From"
                            class="form-control"
                            value="{{ Request::get('price_from') ?? ''}}">
                        <input type="text" name="price_to" aria-label="Last name" 
                        placeholder="To" class="form-control"
                        value="{{ Request::get('price_to') ?? ''}}">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control"
                    value="{{ Request::get('date') ?? ''}}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Variant</th>
                            <th width="150px">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @if (!$productInfo->isEmpty())
                            <?php
                            $page = Request::get('page');
                            $page = empty($page) ? 1 : $page;
                            $sl = ($page - 1) * 10;
                            ?>
                            @foreach ($productInfo as $info)
                                <tr>
                                    <td>{{ ++$sl }}</td>
                                    <td>
                                        {{ $info->title ?? '' }} <br> Created at :
                                        {{ !empty($info->created_at) ? date('d-M-Y', strtotime($info->created_at)) : '' }}
                                    </td>
                                    <td width="180px" class="product-description">
                                        {!! $info->description !!}
                                    </td>
                                    <td>
                                        @if (!empty($productArr[$info->id]))
                                            <dl class="row mb-0" style="height: 80px; overflow: hidden"
                                                id="variant_{{ $info->id }}">
                                                @foreach ($productArr[$info->id] as $varId => $var)
                                                    <dt class="col-sm-3 pb-0">
                                                        {!! $var['title'] !!}
                                                    </dt>
                                                    <dd class="col-sm-9">
                                                        <dl class="row mb-0">
                                                            <dt class="col-sm-4 pb-0">Price :
                                                                {{ !empty($var['stock']) ? number_format($var['price'], 2) : '0.00' }}
                                                            </dt>
                                                            <dd class="col-sm-8 pb-0">InStock :
                                                                {{ !empty($var['stock']) ? number_format($var['stock'], 2) : '0.00' }}
                                                            </dd>
                                                        </dl>
                                                    </dd>
                                                @endforeach
                                            </dl>
                                            <button class="btn btn-sm btn-link variant"data-id="{{ $info->id }}">
                                                Show more
                                            </button>
                                        @endif
                                    </td>
                                    <td width="150px">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('product.edit', $info->id) }}"
                                                class="btn btn-success">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>

                </table>
            </div>

        </div>
        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-8">
                    <?php
                    $start = empty($productInfo->total()) ? 0 : ($productInfo->currentPage() - 1) * $productInfo->perPage() + 1;
                    $end = $productInfo->currentPage() * $productInfo->perPage() > $productInfo->total() ? $productInfo->total() : $productInfo->currentPage() * $productInfo->perPage();
                    ?>
                    <p>Showing {{ $start }} to {{ $end }} out of {{ $productInfo->total() }}</p>
                </div>
                <div class="col-md-4 pull-right">
                    {{ $productInfo->appends(Request::all())->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            $('.product-description').each(function() {
                var str = $(this).text();
                var returnStr = str;
                if (typeof(str) != 'undefined') {
                    var dot = '';
                    if (str.length > 100) {
                        dot = '...';
                    }

                    returnStr = str.substring(0, 100) + dot;
                }
                $(this).text(returnStr);
            });

            $(document).on('click', '.variant', function() {
                var id = $(this).attr('data-id');
                $('#variant_' + id).toggleClass('h-auto');
            });
        });
    </script>

@endsection
