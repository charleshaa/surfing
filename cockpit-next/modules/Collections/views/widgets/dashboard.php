<div>

    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex">
            <strong class="uk-panel-box-header-title uk-flex-item-1">@lang('Collections')</strong>
            @if(count($collections))
            <span class="uk-badge uk-flex uk-flex-middle"><span>{{ count($collections) }}</span></span>
            @endif
        </div>

        @if(count($collections))

            <div class="uk-margin">

                <ul class="uk-list uk-list-space uk-margin-top">
                    @foreach(array_slice($collections, 0, count($collections) > 5 ? 5: count($collections)) as $col)
                    <li>
                        <div class="uk-grid uk-grid-small">
                            <div class="uk-flex-item-1">
                                <a href="@route('/collections/entries/'.$col['name'])">

                                    @if(isset($col['color']) && $col['color'])
                                    <i class="uk-icon-justify uk-icon-list" style="color:{{ $col['color'] }}"></i>
                                    @else
                                    <i class="uk-icon-justify uk-icon-list"></i>
                                    @endif

                                    {{ @$col['label'] ? $col['label'] : $col['name'] }}
                                </a>
                            </div>
                            <div>
                                <a class="uk-link-muted" href="@route('/collections/entry')/{{ $col['name'] }}" title="@lang('Add entry')" data-uk-tooltip="pos:'right'"><i class="uk-icon-plus-circle"></i></a>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>

            </div>

            <div class="uk-panel-box-footer">
                <a href="@route('/collections')">@lang('See all')</a>
            </div>

        @else

            <div class="uk-margin uk-text-center uk-text-muted">

                <p>
                    <img src="@url('collections:icon.svg')" width="30" height="30" alt="Collections" data-uk-svg />
                </p>

                @lang('No collections'). <a href="@route('/collections/collection')">@lang('Create a collection')</a>.

            </div>

        @endif

    </div>

</div>
