var QedShortcodesConfig = {
    "menu": {
        "Edit": "_edit_",
        "General": {"Accordion": "accordion", "Latest Posts": "latest_posts", "Tabs": "tabs"},
        "External Services": {"Google Map": "google_map", "Google Map Embed": "google_map_embed"},
        "Columns": "row"
    },
    "attributes": {
        "accordion": {
            "content": {
                "type": "textarea",
                "value": "[accordion_item title=\"Title 1\" is_active=\"on\"]Lorem ipsum 1[\/accordion_item][accordion_item title=\"Title 2\"]Lorem ipsum 2[\/accordion_item][accordion_item title=\"Title 3\"]Lorem ipsum 3[\/accordion_item]"
            },
            "style": {"type": "dropdown", "value": "with-shadow", "values": ["with-shadow", "with-border"]},
            "css_class": {"type": "text"}
        },
        "latest_posts": {
            "title": {"value": "Latest Posts", "type": "text"},
            "title_underline": {"type": "dropdown", "value": "on", "values": ["on", "off"]},
            "category": {"description": "Filter items from specific category (enter category slug).", "type": "text"},
            "post_ids": {
                "description": "Specify exact ids of items that should be displayed separated by comma.",
                "type": "text"
            },
            "number": {"value": "1", "type": "text"},
            "read_more_text": {"value": "Read more", "type": "text"},
            "words_limit": {"value": "25", "type": "text"},
            "ignore_sticky_posts": {"type": "dropdown", "value": "on", "values": ["on", "off"]},
            "order": {"type": "dropdown", "value": "DESC", "values": ["DESC", "ASC"]},
            "orderby": {
                "type": "dropdown",
                "value": "date",
                "values": ["date", "title", "name", "modified", "rand", "comment_count", "post__in"]
            },
            "css_class": {"type": "text"}
        },
        "tabs": {
            "content": {
                "type": "textarea",
                "value": "[tab_item title=\"Title 1\" is_active=\"on\"]Lorem ipsum 1[\/tab_item][tab_item title=\"Title 2\"]Lorem ipsum 2[\/tab_item][tab_item title=\"Title 3\"]Lorem ipsum 3[\/tab_item]"
            },
            "style": {"type": "dropdown", "value": "with-shadow", "values": ["with-shadow", "with-border"]},
            "css_class": {"type": "text"}
        },
        "google_map": {
            "address": {
                "description": "The address will show up when clicking on the map marker.",
                "type": "text"
            },
            "coordinates": {
                "value": "40.764324,-73.973057",
                "description": "Coordinates separated by comma.",
                "required": true,
                "type": "text"
            },
            "zoom": {"value": 10, "description": "Number in range from 1 up to 21.", "required": true, "type": "text"},
            "height": {"value": "400", "type": "text"},
            "width_mode": {"type": "dropdown", "value": "box-width", "values": ["box-width", "full-width"]},
            "css_class": {"type": "text"}
        },
        "google_map_embed": {
            "src": {"type": "text"},
            "height": {"value": "450", "type": "text"},
            "css_class": {"type": "text"}
        },
        "row": {"columns": {"value": "2", "type": "text"}, "css_class": {"type": "text"}}
    }
};