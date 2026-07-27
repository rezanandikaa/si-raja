<form id="beForm" method="POST" action="{{ $data['_be_action'] }}" enctype="multipart/form-data" autocomplete="off" data-parsley-validate novalidate>
    @csrf
    @method($data['_be_method'])
    @forelse ($data['fields'] as $key => $item)
        @php
            $maxlength = isset($item['maxlength']) ? $item['maxlength'] : 100;
            $readonly = isset($item['read_only']) ? ($item['read_only'] ? 'disabled': '') : '';
            $uppercase = isset($item['uppercase']) ? ($item['uppercase'] ? 'uppercase' : '') : 'uppercase';
        @endphp

        @switch($item['type'])
            {{-- text --}}
            @case('text')
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <input type="text"
                        id="{{$key}}"
                        name="{{$item['name']}}"
                        class="form-control {{ $uppercase }}"
                        onblur="textBlur('{{$key}}')"
                        placeholder="{{$item['placeholder']}}"
                        value="{{$item['value'] ?? ''}}"
                        maxlength="{{$maxlength}}"
                        {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                        {!! $item['parsley'] ?? '' !!}
                        {{$readonly}} />
                </div>
                @break
            
            
            {{-- hidden --}}
            @case('hidden')
                <div class="form-group">
                    <label for="{{$key}}" style="display: none">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <input type="hidden"
                        id="{{$key}}"
                        name="{{$item['name']}}"
                        class="form-control {{ $uppercase }}"
                        onblur="textBlur('{{$key}}')"
                        placeholder="{{$item['placeholder']}}"
                        value="{{$item['value'] ?? ''}}"
                        maxlength="{{$maxlength}}"
                        {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                        {!! $item['parsley'] ?? '' !!}
                        {{$readonly}} />
                </div>
                @break

            {{-- password --}}
            @case('password')
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <input type="password"
                        id="{{$key}}"
                        name="{{$item['name']}}"
                        class="form-control"
                        onblur="textBlur('{{$key}}')"
                        placeholder="{{$item['placeholder']}}"
                        value="{{$item['value'] ?? ''}}"
                        maxlength="{{$maxlength}}"
                        {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                        {!! $item['parsley'] ?? '' !!}
                        {{$readonly}} />
                </div>
                @break

            {{-- number --}}
            @case('number')
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <input type="number"
                        id="{{$key}}"
                        name="{{$item['name']}}"
                        class="form-control"
                        onblur="textBlur('{{$key}}')"
                        placeholder="{{$item['placeholder']}}"
                        value="{{$item['value'] ?? ''}}"
                        maxlength="{{$maxlength}}"
                        {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                        {!! $item['parsley'] ?? '' !!}
                        {{$readonly}} />
                </div>
                @break

            {{-- decimal --}}
            @case('decimal')
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <input type="number"
                        id="{{$key}}"
                        name="{{$item['name']}}"
                        class="form-control"
                        onblur="textBlur('{{$key}}')"
                        placeholder="{{$item['placeholder']}}"
                        value="{{$item['value'] ?? ''}}"
                        maxlength="{{$maxlength}}"
                        step="0.01"
                        {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                        {!! $item['parsley'] ?? '' !!}
                        {{$readonly}} />
                </div>
                @break

            {{-- email --}}
            @case('email')
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <input type="email"
                        id="{{$key}}"
                        name="{{$item['name']}}"
                        class="form-control"
                        onblur="textBlur('{{$key}}')"
                        placeholder="{{$item['placeholder']}}"
                        value="{{$item['value'] ?? ''}}"
                        maxlength="{{$maxlength}}"
                        {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                        {!! $item['parsley'] ?? '' !!}
                        {{$readonly}} />
                </div>
                @break

            {{-- checkbox --}}
            @case('checkbox')
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <br/>
                    <label class="fancy-checkbox">
                        <input type="checkbox"
                            id="{{$key}}"
                            name="{{$item['name']}}"
                            class="form-control"
                            value="1"
                            {{(isset($item['value']) ? ($item['value'] == 1 ? 'checked':''):'')}}
                            {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                            {!! $item['parsley'] ?? '' !!}
                            {{$readonly}} />
                        <span>{{isset($item['label_extra']) ? $item['label_extra'] : '(Centang untuk mengaktifkan)'}}</span>
                    </label>
                </div>
                @break

            {{-- file --}}
            @case('file')
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <div class="custom-file">
                        <input type="file"
                            id="{{$key}}"
                            name="{{$item['name']}}{{(isset($item['multiple']) ? ($item['multiple'] == true ? '[]' : ''): '')}}"
                            class="custom-file-input"
                            placeholder="{{$item['placeholder']}}"
                            {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                            {{(isset($item['multiple']) ? ($item['multiple'] == true ? 'multiple' : ''): '')}}
                            {!! $item['parsley'] ?? '' !!}
                            {{$readonly}} />
                        <label class="custom-file-label" for="{{$key}}">Pilih Berkas</label>
                    </div>
                </div>
                @break

            @case('x')
                {{-- data --}}
                @php
                    $opt_data_table = isset($item['data_table']) ? $item['data_table'] : '';
                    $opt_data_condition = isset($item['data_condition']) ? $item['data_condition'] : '';
                    $opt_data_extra = isset($item['data_extra']) ? $item['data_extra'] : '';
                @endphp
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <select class="custom-select" id="{{$key}}" name="{{$key}}" {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}} data-table="{{$opt_data_table}}" data-condition="{{$opt_data_condition}}" data-extra="{{$opt_data_extra}}">
                        <option value="">-- Pilih --</option>
                        @foreach ($item['options'] as $option)
                            <option value="{{$option['id']}}" {!! (isset($item['value']) ? ($item['value'] == $option['id'] ? 'selected="selected"':''):'') !!}>{{$option['label']}}</option>
                        @endforeach
                    </select>
                </div>
                @break

            {{-- data --}}
            @case('data')
                @php
                    $opt_data_table = isset($item['data_table']) ? $item['data_table'] : '';
                    $opt_data_condition = isset($item['data_condition']) ? $item['data_condition'] : '';
                    $opt_data_extra = isset($item['data_extra']) ? $item['data_extra'] : '';
                @endphp
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <div class="c_multiselect">
                        <select class="multiselect multiselect-custom" id="{{$key}}" name="{{$key}}" {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}} data-table="{{$opt_data_table}}" data-condition="{{$opt_data_condition}}" data-extra="{{$opt_data_extra}}">
                            <option value="">-- Pilih --</option>
                            @foreach ($item['options'] as $option)
                                <option value="{{$option['id']}}" {!! (isset($item['value']) ? ($item['value'] == $option['id'] ? 'selected="selected"':''):'') !!}>{{$option['label']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @break

            {{-- data-multi --}}
            @case('data-multi')
                @php
                    $opt_data_table = isset($item['data_table']) ? $item['data_table'] : '';
                    $opt_data_condition = isset($item['data_condition']) ? $item['data_condition'] : '';
                    $opt_data_extra = isset($item['data_extra']) ? $item['data_extra'] : '';
                    $item_values = (is_json($item['value']) ? $item['value'] : '[]');
                @endphp
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <div class="c_multiselect">
                        <select class="multiselect multiselect-custom" multiple="multiple" id="{{$key}}" name="{{$key}}[]" {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}} data-table="{{$opt_data_table}}" data-condition="{{$opt_data_condition}}" data-extra="{{$opt_data_extra}}">
                            @foreach ($item['options'] as $option)
                                <option value="{{$option['id']}}" {!! (isset($item['value']) ? (in_array($option['id'], json_decode($item_values, true)) ? 'selected="selected"':''):'') !!}>{{$option['label']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @break

            {{-- select --}}
            @case('select')
                {{-- <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <select class="custom-select" id="{{$key}}" name="{{$key}}" {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}>
                        <option value="">-- Pilih --</option>
                        @foreach ($item['options'] as $option)
                            <option value="{{$option}}" {!! (isset($item['value']) ? ($item['value'] == $option ? 'selected="selected"':''):'') !!}>{{$option}}</option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <div class="c_multiselect">
                        <select class="multiselect multiselect-custom" id="{{$key}}" name="{{$key}}" {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}>
                            <option value="">-- Pilih --</option>
                            @foreach ($item['options'] as $option)
                                <option value="{{$option}}" {!! (isset($item['value']) ? ($item['value'] == $option ? 'selected="selected"':''):'') !!}>{{$option}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @break

            {{-- select-multi --}}

            {{-- text-area --}}
            @case('text-area')
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <textarea type="text"
                        id="{{$key}}"
                        name="{{$item['name']}}"
                        class="form-control {{ $uppercase }}"
                        onblur="textBlur('{{$key}}')"
                        placeholder="{{$item['placeholder']}}"
                        rows="3"
                        maxlength="{{$maxlength}}"
                        {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                        {!! $item['parsley'] ?? '' !!}
                        {{$readonly}}>{{$item['value'] ?? ''}}</textarea>
                </div>
                @break

            @case('image')
                <div class="form-group">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <div class="controls">
                        @php
                            $image = 'http://via.placeholder.com/'.(isset($item['image_width']) ? $item['image_width'] : 400).'x'.(isset($item['image_height']) ? $item['image_height'] : 400);
                        @endphp
                        <img src="{{ ( isset($item['value']) ? ( $item['value'] == 0 ? $image : get_image($item['value'])): $image) }}" id="uploaded_{{$key}}" class="img-responsive img-circle" /><br />
                        <div class="overlay">
                            <div class="text">Click to Change Image</div>
                        </div>
                        <input type="hidden" id="data-image-{{$key}}" name="{{$item['name']}}" value="{{$item['value'] ?? 0}}" />
                        <input type="file" id="{{$key}}" accept="image/*" onchange="imageChange(this, '{{$key}}', '#data-image-{{$key}}', {{(isset($item['image_width']) ? $item['image_width'] : 400)}}, {{(isset($item['image_height']) ? $item['image_height'] : 400)}}, '{{(isset($item['reference_name']) ? $item['reference_name'] : '')}}', '{{(isset($item['reference_id']) ? $item['reference_id'] : 0)}}')" class="form-control" placeholder="{{$item['placeholder']}}" value="{{$item['value'] ?? ''}}" maxlength="{{$maxlength}}" {{(isset($item['required']) ? ($item['required'] == true ? 'required':''): '')}} {!! (isset($item['required']) ? ($item['required'] == true ? "required data-validation-required-message='{$item['validate_message']}'" : ""): "") !!} {{$readonly}}>
                    </div>
                </div>
                @break

            {{-- text --}}
            @case('map-marker')
                <div class="form-group">
                    <div id="map-{{$key}}" class="map-leaf"></div>
                </div>
                <div class="form-group" style="display: none;">
                    <label for="{{$key}}">{{$item['label']}} {!!(isset($item['required']) ? ($item['required'] == true ? "<span class='text-danger'>*</span>":''): '')!!}</label>
                    <input type="text"
                        id="{{$key}}"
                        name="{{$item['name']}}"
                        class="form-control map-input"
                        onblur="textBlur('{{$key}}')"
                        placeholder="{{$item['placeholder']}}"
                        value="{{$item['value'] ?? ''}}"
                        maxlength="{{$maxlength}}"
                        {{(isset($item['required']) ? ($item['required'] == true ? 'required' : ''): '')}}
                        {!! $item['parsley'] ?? '' !!}
                        {{$readonly}} data-parsley-excluded="true"/>
                </div>
                @break

            @case('tabs')
                {{-- @include('baduyengine.component.detail_table', $item) --}}
                @break

            @default
        @endswitch
    @empty
    {{-- if null --}}
    @endforelse

    <a href="{{ url()->previous() }}" class="btn btn-danger">Batal</a>
    <button type="submit" class="btn btn-{{ (isset($data['_be_btn_varian']) ? $data['_be_btn_varian'] : 'primary') }}">{{ (isset($data['_be_btn_label']) ? $data['_be_btn_label'] : 'Simpan') }}</button>
</form>
<div class="modal fade" id="modalUpload" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalLabel">Crop Image Before Upload</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="img-container">
                  <div class="row">
                      <div class="col-md-8">
                          <img src="" id="sampleImage" />
                      </div>
                      <div class="col-md-4">
                          <div class="preview"></div>
                      </div>
                  </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" id="crop">Crop</button>
            </div>
      </div>
    </div>
</div>
