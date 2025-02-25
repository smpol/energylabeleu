{if isset($energy_class)}
    <div {if $css} class="{$css}" {/if} style="
    {if $width}width:{$width}px;{/if}
    {if $height}height:{$height}px;{/if}
    ">
        <img src="{$smarty.const._PS_BASE_URL_}/modules/energylabeleu/views/img/{$energy_class}.svg"
            alt="Energy Class {$energy_class}">
    </div>
{/if}