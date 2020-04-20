import 'bootstrap/js/dist/util';
import 'bootstrap/js/dist/collapse';
import 'bootstrap/js/dist/dropdown';
import 'bootstrap/js/dist/tooltip';
import 'bootstrap/js/dist/popover';
import './plugins/wecodeart-Component';
import './plugins/wecodeart-JSManager';
import './plugins/wecodeart-Template';
import createParams from './helpers/createParams';
import parseJSONData from './helpers/parseData';
import hasScrollbar, { handleBodyJSClass, handleDocumentScrollbar } from './helpers/HasScrollbar';

const { addAction } = wp.hooks;

addAction('wecodeart.route', 'wecodeart/developer/log', filterLog, 10);
function filterLog(route, func, args) {
	const { isDevMode = false } = wecodeart;
	if (isDevMode) {
		console.log('Loaded: ', route, '::', func);
		if (args) console.log(args);
	}
}

(function (wecodeart, $) {
	/**
	 * Base WCA Functions
	 * @since 3.6
	 */
	wecodeart.plugins = {};
	wecodeart.fn = {
		hasScrollbar: hasScrollbar,
		createParams: createParams,
		getOptions: parseJSONData,
	};
	wecodeart.routes = {
		common: {
			init: () => {
				handleBodyJSClass();
				handleDocumentScrollbar();
				window.onresize = handleDocumentScrollbar;
			},
			complete: () => {
				const { fn: { getOptions } } = wecodeart;

				$('[data-toggle="tooltip"]').tooltip();
				const customTooltips = document.querySelectorAll('.has-tooltip');
				for (let item of customTooltips) {
					const $item = $(item);
					const options = getOptions(item.getAttribute('options'));
					const { type } = options;
					delete options.type;
					if (type === 'popover') {
						$item.popover(options);
					}

					if (type === 'tooltip') {
						$item.tooltip(options);
					}
				}
			}
		}
	};
}).apply(this, [window.wecodeart, jQuery]);