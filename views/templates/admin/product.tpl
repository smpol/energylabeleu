{if isset($energy_class)}
    {assign var="selected_class" value=$energy_class}
{else}
    {assign var="selected_class" value='N/A'}
{/if}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i> {l s='Energy Class'}
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="energy_class">
            {l s='Select Energy Class'}
        </label>
        <div class="col-lg-6">
            <select name="energy_class" id="energy_class" class="form-control">
                {foreach from=$energy_classes item=energy}
                    <option value="{$energy}" {if $selected_class == $energy}selected{/if}>
                        {$energy}
                    </option>
                {/foreach}
            </select>
            <p class="help-block">{l s='Or select N/A if you don\'t have information'}</p>
        </div>
    </div>

    <div class="panel-footer">
        <button type="submit" class="btn btn-primary">
            <i class="process-icon-save"></i> {l s='Save'}
        </button>
    </div>
</div>