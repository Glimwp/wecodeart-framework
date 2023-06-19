/**
 * WordPress dependencies
 */
const {
	i18n: {
		__,
	},
	hooks: {
		addFilter
	},
} = wp;

import { Manager } from './Components';

import './../../../scss/admin/plugins/index.scss';

addFilter('wecodeart.admin.tabs.plugins', 'wecodeart/plugins/admin/panel', optionsPanel);
function optionsPanel(panels) {
	return [{
		name: 'manager',
		title: __('Plugins Manager', 'wecodeart'),
		render: (props) => <Manager {...props} />
	}, ...panels];
}