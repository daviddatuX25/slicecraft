{{-- ============================================================
NOTE (change NOT in the worksheet instructions):
The dashboard page references <x-box> with named slots boxtitle, boxcontent,
boxfooter, but the worksheet never gives a step to create this component.
Without it Laravel throws: "Component [box] not found". I created it to render
an INSPINIA "ibox" panel (title / content / footer) from those slots.
============================================================ --}}
<div class="ibox float-e-margins">
    <div class="ibox-title">
        {{ $boxtitle ?? '' }}
    </div>
    <div class="ibox-content">
        {{ $boxcontent ?? '' }}
    </div>
    <div class="ibox-footer">
        {{ $boxfooter ?? '' }}
    </div>
</div>
