<div class="jet-woo-product-gallery-settings-page jet-woo-product-gallery-settings-page__avaliable-addons">
	<div class="avaliable-widgets">
		<div class="avaliable-widgets__option-info">
			<div class="avaliable-widgets__option-info-name"><?php _e( 'Available Widgets', 'jet-woo-product-gallery' ); ?></div>
			<div class="avaliable-widgets__option-info-desc"><?php _e( 'List of widgets that will be available when editing the page', 'jet-woo-product-gallery' ); ?></div>
		</div>
		<div class="avaliable-widgets__controls">
			<div
				class="avaliable-widgets__control"
				v-for="(option, index) in pageOptions.product_gallery_available_widgets.options"
			>
				<cx-vui-switcher
					:key="index"
					:name="`product-gallery-avaliable-widget-${option.value}`"
					:label="option.label"
					:wrapper-css="[ 'equalwidth' ]"
					return-true="true"
					return-false="false"
					v-model="pageOptions.product_gallery_available_widgets.value[option.value]"
				>
				</cx-vui-switcher>
			</div>
		</div>
	</div>
</div>
