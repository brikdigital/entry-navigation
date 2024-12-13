// INTRODUCING: Probably the most disgusting patch I've ever written.
// It fetches all of our form elements in probably the worst way I could've thought of (in hindsight,
// somehow fetching them with just the parent selector might've worked too), then removes Craft's form listener,
// and then re-adds it, ignoring events that come from our form elements.
const origInit = Craft.FormObserver.prototype.init;
Craft.FormObserver.prototype.init = function(container, callback) {
    // Call the original constructor
    origInit.bind(this)(container, callback);

    // Array of the form elements that belong to our plugin container
    const ourFormElements = [];
    for (const element of container[0]) {
        const parent = $(element).closest('.navigation-element-sidebar');

        if (parent.length !== 0) {
            ourFormElements.push(element.outerHTML);
        }
    }

    // Now disable all event listeners on the form container
    container.off();

    // Everything aside from the first if statement in the callback is taken from here:
    // <https://github.com/craftcms/cms/blob/d622b6183baaeef7019767a74cb1138e5023dbdd/src/web/assets/cp/src/js/FormObserver.js#L40-L48>
    this.addListener(this.$container, 'change,input,keypress,keyup', (ev) => {
        if (ourFormElements.includes(ev.target.outerHTML)) {
            console.log("[entry-navigation] editing-related event caught in one of our elements, ignoring");
            return;
        }

        if (this.isActive) {
            // slow down when actively typing
            if (['keypress', 'keyup'].includes(ev.type)) {
                this._recentKeypress = true;
            }
            this._checkFormAfterDelay();
        }
    });
}
