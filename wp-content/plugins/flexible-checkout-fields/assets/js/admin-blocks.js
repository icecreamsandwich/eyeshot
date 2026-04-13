( function( wp ) {
	const { addFilter }                  = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { Fragment, createElement }    = wp.element;
	const { Notice, Button }             = wp.components;
	const { InspectorControls }          = wp.blockEditor;

	const withProSidebarNotice = createHigherOrderComponent( ( BlockEdit ) => {
		return ( props ) => {
			const isCheckoutBlock = props.name.indexOf( 'woocommerce/checkout' ) === 0;

			if ( ! isCheckoutBlock ) {
				return createElement( BlockEdit, props );
			}

			const data = window.fcf_block_data || {};

			const openPluginModal = () => {
				const slug    = data.plugin_slug || 'woocommerce';
				const baseUrl = data.admin_url;

				const href = `${ baseUrl }?tab=plugin-information&plugin=${ slug }&TB_iframe=true&width=772&height=550`;

				if ( window.tb_show ) {
					window.tb_show( data.button_text, href );
				} else {
					window.open( href, '_blank' );
				}
			};

			return createElement(
				Fragment,
				{},
				createElement( BlockEdit, props ),

				createElement(
					InspectorControls,
					{},
					createElement(
						'div',
						{
							className: 'fcf-sidebar-info-wrapper',
							style: { borderBottom: '1px solid #e0e0e0', borderTop: '1px solid #e0e0e0' },
						},
						createElement(
							Notice,
							{
								status: 'info',
								isDismissible: false,
								className: 'fcf-sidebar-notice',
							},
							createElement( 'p', { style: { marginTop: 0 } }, data.message ),
							createElement(
								Button,
								{
									variant: 'primary',
									onClick: openPluginModal,
									style: { marginTop: '10px', width: '100%', justifyContent: 'center' },
								},
								data.button_text,
							),
						),
					),
				),
			);
		};
	}, 'withProSidebarNotice' );

	addFilter(
		'editor.BlockEdit',
		'flexible-checkout-fields/add-sidebar-notice',
		withProSidebarNotice,
	);

} )( window.wp );
