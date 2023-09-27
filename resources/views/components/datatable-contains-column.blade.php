<style>
    .has-tooltip{
        position:relative;
    }
    .has-tooltip:hover .tooltip,
    .has-tooltip:focus .tooltip,
    .has-tooltip.hover .tooltip{
        opacity: 1;
        transform: translate(-50%, -100%) scale(1) rotate(0deg);
        pointer-events: inherit;
    }	
    .tooltip{
        display: block;
        position: absolute;
        top: -5px;
        left: 170%;
        transform: translate(-50%, -50%) scale(0.75) rotate(5deg);
        transform-origin: bottom center;
        padding: 10px 30px;
        border-radius: 5px;
        background: rgba(0,0,0,0.75);
        text-align: center;
        color: white;
        min-width:185px;
        transition: 0.15s ease-in-out;
        opacity: 0;
        pointer-events: none;
        z-index: 5;
    }
    .tooltip::after{
        content: '';
		display: block;
		margin: 0 auto;
		widtH: 0;
		height: 0;
		border: 5px solid transparent;
		border-top: 5px solid rgba(0,0,0,0.75);
		position: absolute;
		bottom: 0;
		left: 10%;
		transform: translate(-50%, 100%);
    }
	
	

</style>
<div class="flex justify-center items-center ml-5">
    <div class="icon-demo-content has-tooltip">
        @if($getState() == 'Flight')
            <i class="mdi mdi-airplane"></i>
            <span class='tooltip'><p>Flight</p></span>
        @elseif($getState() == 'Hotel')
            <i class="mdi mdi-home"></i>
            <span class='tooltip'><p>Hotel</p></span>
        @elseif($getState() == 'Transfer')
            <i class="mdi mdi-transit-transfer"></i>
            <span class='tooltip'><p>Transfer</p></span>
        @endif
    </div>
</div>
