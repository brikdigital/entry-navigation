// TODO: Maybe, potentially, rewrite to use Garnish at some point.

$('.navigation-element-sidebar button[id="saveButton"]').click(function (e) {
    e.preventDefault();

    // excuse the mess
    // TODO(lexisother): Figure out a more jQuery:tm: Agnostic method for this
    const nodeId = $(this).parents("[data-id]").attr("data-id");
    const title = $(this).parent().parent().parent().find("#nodeTitle").val();
    const parent = $(this).parent().parent().parent().find('#parent').val();

    return Craft.sendActionRequest('POST', "entry-navigation/nodes/edit-node", {
        data: {
            nodeId,
            title,
            parent
        }
    })
        .then(() => {
            window.location.reload();
        })
        .catch(err => {
            Craft.cp.displayError(err.response.data.message);
        })
});

$('.navigation-element-sidebar button[id="deleteButton"]').click(function (e) {
    e.preventDefault()

    const nodeId = $(this).parents("[data-id]").data("id");

    return Craft.sendActionRequest('POST', 'entry-navigation/nodes/delete-node', {
        data: {
            nodeId,
            siteId: Craft.siteId
        }
    })
        .then(() => {
            window.location.reload();
        })
        .catch(err => {
            Craft.cp.displayError(err.response.data.message);
        })
})

$('.navigation-element-sidebar #navSelect').change(function (e) {
    $(".navigation-element-sidebar #submenu").empty();

    const navId = $(this).find(':selected').data('id');
    if (navId === 0) {
        return;
    }

    Craft.sendActionRequest('POST', $(this).data('action'), {
        data: {
            navId
        }
    })
        .then(res => {
            buildSubmenu(res.data);
        });
});

// I have absolutely no shame, nor regrets.
// Well, maybe some regrets.
function buildSubmenu(data) {
    const options = data.options;

    const menu = [
        "<div class='select'>",
        "  <select id='parentSelect'>"
    ];
    for (let option of options) {
        menu.push(`    <option value='${option.value}'>${option.label}</option>`);
    }
    menu.push("  </select>");
    menu.push(...[
        "  </select>",
        "</div>"
    ]);

    $(".navigation-element-sidebar #submenu").append($(menu.join("\n")));
    $(".navigation-element-sidebar #submenu").append(
        $("<button class='btn submit icon add'>Add</button>").click((e) => {
            e.preventDefault();

            const node = {
                // Meta
                siteId: Craft.siteId,

                // Nav data
                navId: $(".navigation-element-sidebar #navSelect").find(':selected').data('id'),
                parentId: $(".navigation-element-sidebar #parentSelect").find(':selected').val(),

                // Entry data
                elementSiteId: Craft.siteId,
                elementId: window.__ENTRY_ID__,
                title: $("input[type='text'][id='title']").val(),
                type: "craft\\elements\\Entry",
                url: window.__ENTRY_URL__,
                newWindow: "", // TODO: make this an actual option
            };

            Craft.sendActionRequest('POST', 'navigation/nodes/add-nodes', {
                data: {
                    nodes: [node]
                }
            })
                .then(() => {
                    window.location.reload();
                });
        })
    );
}
