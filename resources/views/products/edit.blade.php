@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
    </div>

    <section>
        <form id="productEditForm" method="post" class="card-header">
            <input type="hidden" name="product_id" value="{{ $product->id }}" />
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="title">Product Name</label>
                                <input type="text" name="title" id="title" placeholder="Product Name"
                                    class="form-control" value="{!! $product->title ?? '' !!}">
                            </div>
                            <div class="form-group">
                                <label for="sku">Product SKU</label>
                                <input type="text" id="sku" name="sku" placeholder="Product SKU"
                                    class="form-control" value="{!! $product->sku ?? '' !!}">
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" cols="30" rows="4" class="form-control">
                                {!! $product->description ?? '' !!}
                            </textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Media</h6>
                        </div>
                        <div class="card-body border">
                            {{-- <vue-dropzone ref="myVueDropzone" id="dropzone" :options="dropzoneOptions"></vue-dropzone> --}}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Variants</h6>
                        </div>
                        <div class="card-body">
                            @if (!empty($variantArr))
                                @php $vSl = 0;  @endphp
                                @foreach ($variantArr as $vId => $v)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Option</label>
                                                <select id="variant_{{ ++$vSl }}" name="variant[{{ $vSl }}][var]"
                                                    class="form-control">
                                                    @if (!empty($variantList))
                                                        @foreach ($variantList as $varId => $var)
                                                            <option value="{{ $varId }}"
                                                                {{ $vId == $varId ? 'selected' : '' }}>
                                                                {{ $var }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="float-right text-primary"
                                                    style="cursor: pointer;">Remove</label>
                                                <label for="">.</label>
                                                
                                                <input name="variant[{{ $vSl }}][p_var]" class="form-control"
                                                    value="{{ implode(' ', $v) }}" />


                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Option</label>
                                            <select id="variant_1" name="variant[1][var]" class="form-control">
                                                @if (!empty($variantList))
                                                    @foreach ($variantList as $varId => $var)
                                                        <option value="{{ $varId }}">
                                                            {{ $var }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="float-right text-primary" style="cursor: pointer;">Remove</label>
                                            <label for="">.</label>
                                            <input name="variant[1][p_var]" class="form-control" />


                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary">Add another option</button>
                        </div>

                        <div class="card-header text-uppercase">Preview</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <td>Variant</td>
                                            <td>Price</td>
                                            <td>Stock</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($productVarArr))
                                            @foreach($productVarArr as $pVarId => $pVar)
                                                <tr>
                                                    <td>{{ $pVar['title'] ?? '' }}</td>
                                                    <td>
                                                        <input type="text" name="pr_var[{{$pVar['var']}}][price]" class="form-control" 
                                                        value="{{$pVar['price'] ?? ''}}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="pr_var[{{$pVar['var']}}][stock]" class="form-control" 
                                                        value="{{ $pVar['stock'] ?? ''}}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3"></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>

        <button type="button" class="btn btn-lg btn-primary btn-submit">Save</button>
        <button type="button" class="btn btn-secondary btn-lg">Cancel</button>
    </section>

    <script>
        $(function() {
            $(document).on("click", ".btn-submit", function(e) {
                e.preventDefault();
                var formData = new FormData($('#productEditForm')[0]);

                var options = {
                    closeButton: true,
                    debug: false,
                    positionClass: "toast-bottom-right",
                    onclick: null,
                };

                $.ajax({
                    url: "{{ url('product/updateProduct') }}",
                    type: 'POST',
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('.btn-submit').prop('disabled', true);
                        // App.blockUI({
                        //     boxed: true
                        // });
                    },
                    success: function(res) {
                        $('.btn-submit').prop('disabled', false);
                        toastr.success(res.message, res.heading, options);
                        location.replace("{{ route('product.index') }}");

                    },
                    error: function(jqXhr, ajaxOptions, thrownError) {
                        if (jqXhr.status == 400) {
                            var errorsHtml = '';
                            var errors = jqXhr.responseJSON.message;
                            $.each(errors, function(key, value) {
                                errorsHtml += '<li>' + value + '</li>';
                            });
                            toastr.error(errorsHtml, jqXhr.responseJSON.heading, options);
                        } else if (jqXhr.status == 401) {
                            toastr.error(jqXhr.responseJSON.message, '', options);
                        } else {
                            toastr.error('Error', 'Something went wrong', options);
                        }
                        $('.btn-submit').prop('disabled', false);
                        // App.unblockUI();
                    }
                });
            });
        });
    </script>
@endsection
