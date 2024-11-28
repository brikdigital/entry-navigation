console.log(window.__ENTRY_ID__)
console.log(window.__ENTRY_URL__);

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
        })
})

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
            console.log("hi");

            const node = {
                // Meta
                siteId: Craft.siteId,

                // Nav data
                navId: $(".navigation-element-sidebar #navSelect").find(':selected').data('id'),
                parentId: $(".navigation-element-sidebar #parentSelect").find(':selected').val(),

                // Entry data
                // elementId: $(".heading:contains('Id')").siblings(".value").text(),
                elementSiteId: Craft.siteId,
                elementId: window.__ENTRY_ID__,
                title: $("input[type='text'][id='title']").val(),
                type: "craft\\elements\\Entry",
                url: window.__ENTRY_URL__,
                newWindow: "", // TODO: make this an actual option
            }

            Craft.sendActionRequest('POST', 'navigation/nodes/add-nodes', {
                data: {
                    nodes: [node]
                }
            })
                .then(res => {
                    Craft.cp.displayNotice(res.data.message);
                    // TODO: Make this also revert the navSelect
                    $(".navigation-element-sidebar #submenu").empty();
                })
        })
    )
}
