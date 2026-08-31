{{-- ============================================================
NOTE (change NOT in the worksheet instructions):
The dashboard page references <x-widget> with named slots w1, w2, w3, but
the worksheet never gives a step to create this component. Without it Laravel
throws: "Component [widget] not found". I created it to render the three
sidebar-independent stat boxes (one per slot) using the INSPINIA "ibox" widget
style.
============================================================ --}}
<div class="row">
    <div class="col-lg-4">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                {{ $w1 ?? '' }}
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                {{ $w2 ?? '' }}
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                {{ $w3 ?? '' }}
            </div>
        </div>
    </div>
</div>
