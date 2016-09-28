<div>

    <ul class="uk-breadcrumb">
        <li><a href="@route('/regions')">@lang('Regions')</a></li>
        <li class="uk-active" data-uk-dropdown="mode:'click'">

            <a><i class="uk-icon-bars"></i> {{ @$region['label'] ? $region['label']:$region['name'] }}</a>

            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Actions')</li>
                    <li><a href="@route('/regions/region/'.$region['name'])">@lang('Edit')</a></li>
                </ul>
            </div>

        </li>
    </ul>

    @if(isset($region['description']) && $region['description'])
    <div class="uk-text-muted uk-margin uk-panel-box">
        <div class="uk-grid uk-grid-small">
            <div><i class="uk-icon-info-circle"></i></div>
            <div class="uk-flex-item-1">{{ $region['description'] }}</div>
        </div>
    </div>
    @endif

    @if(isset($region['color']) && $region['color'])
    <style>
        .app-header { border-top: 8px {{ $region['color'] }} solid; }
    </style>
    @endif

    <div class="uk-margin-top" riot-view>

        <div class="uk-alert" if="{ !fields.length }">
            @lang('No fields defined'). <a href="@route('/regions/region')/{ region.name }">@lang('Define region fields').</a>
        </div>

        <div class="uk-grid">

            <div class="uk-width-medium-3-4">

                <h3>{ region.label || region.name }</h3>

                <ul class="uk-tab uk-margin uk-flex uk-flex-center" show="{ App.Utils.count(groups) > 1 }">
                    <li class="{ !group && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get('All') }</a></li>
                    <li class="{ group==parent.group && 'uk-active'}" each="{group, items in groups}" if="{ items.length }"><a class="uk-text-capitalize" onclick="{ toggleGroup }">{ App.i18n.get(group) }</a></li>
                </ul>

                <br>

                <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

                    <div class="uk-grid uk-grid-match uk-grid-gutter">

                        <div class="uk-width-medium-{field.width}" each="{field,idx in fields}" show="{!parent.group || (parent.group == field.group) }" no-reorder>

                            <div class="uk-panel">

                                <label class="uk-text-bold">
                                    { field.label || field.name }
                                    <span if="{ field.localize }" class="uk-icon-globe" title="@lang('Localized field')" data-uk-tooltip="pos:'right'"></span>
                                </label>

                                 <div class="uk-margin uk-text-small uk-text-muted">
                                    { field.info || ' ' }
                                </div>

                                <div class="uk-margin">
                                    <cp-field field="{ field }" bind="data.{field.localize && parent.lang ? (field.name+'_'+parent.lang):field.name }" cls="uk-form-large"></cp-field>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="uk-margin-large-top">
                        <button class="uk-button uk-button-large uk-button-primary uk-margin-right">@lang('Save')</button>
                        <a href="@route('/regions')">@lang('Close')</a>
                    </div>

                </form>
            </div>

            <div class="uk-width-medium-1-4 uk-flex-order-first uk-flex-order-last-medium">

                <div class="uk-panel">

                    <div class="uk-margin uk-form" if="{ languages.length }">

                        <div class="uk-width-1-1 uk-form-select">

                            <label class="uk-text-small">@lang('Language')</label>
                            <div class="uk-margin-small-top">{ lang || 'Default' }</div>

                            <select bind="lang">
                                <option value="">@lang('Default')</option>
                                <option each="{language,idx in languages}" value="{language}">{language}</option>
                            </select>
                        </div>

                    </div>

                    <div class="uk-margin">
                        <label class="uk-text-small">@lang('Last Modified')</label>
                        <div class="uk-margin-small-top uk-text-muted"><i class="uk-icon-calendar uk-margin-small-right"></i> {  App.Utils.dateformat( new Date( 1000 * region._modified )) }</div>
                    </div>

                </div>

            </div>

        </div>


        <script type="view/script">

            var $this = this;

            this.mixin(RiotBindMixin);

            this.region    = {{ json_encode($region) }};
            this.fields    = this.region.fields;

            this.data      = this.region.data || {};

            this.languages = App.$data.languages;
            this.groups       = {main:[]};
            this.group        = 'main';

            // fill with default values
            this.fields.forEach(function(field){

                if ($this.data[field.name] === undefined) {
                    $this.data[field.name] = field.options && field.options.default || null;
                }

                if (field.type == 'password') {
                    $this.data[field.name] = '';
                }

                if (field.group && !$this.groups[field.group]) {
                    $this.groups[field.group] = [];
                } else if (!field.group) {
                    field.group = 'main';
                }

                $this.groups[field.group || 'main'].push(field);
            });

            if (!this.groups[this.group].length) {
                this.group = Object.keys(this.groups)[1];
            }

            this.on('mount', function(){

                // bind clobal command + save
                Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                    e.preventDefault();
                    $this.submit();
                    return false;
                });
            });

            toggleGroup(e) {
                this.group = e.item && e.item.group || false;
            }

            submit() {

                App.callmodule('regions:updateRegion',[this.region.name, {data:this.data}]).then(function(data) {

                    if (data.result) {

                        App.ui.notify("Saving successful", "success");

                        $this.data = data.result.data;

                        $this.fields.forEach(function(field){

                            if (field.type == 'password') {
                                $this.data[field.name] = '';
                            }
                        });

                        $this.update();

                    } else {
                        App.ui.notify("Saving failed.", "danger");
                    }
                });
            }

        </script>

    </div>

</div>
