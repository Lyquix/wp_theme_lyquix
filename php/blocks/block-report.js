jQuery(document).ready(function ($) {
    $.ajaxSetup({ headers: { 'X-WP-Nonce': lqxBlockReportObj.nonce } });

    const blockTypeField = $('#acf-field_6729f97d9e5be');
    const settingField = $('#acf-field_6729fac39e5bf');
    const fields = {
        presetField: $('#acf-field_6729fc32b2daa'),
        styleField: $('#acf-field_6729fc4bb2dab')
    };

    handleFieldChange();

    // Attach individual change event listeners to each field
    blockTypeField.on('change', function () {
        handleFieldChange();
    });

    settingField.on('change', function () {
        handleFieldChange();
    });

    fields['presetField'].on('change', function () {
        searchPosts();
    })

    fields['styleField'].on('change', function () {
        searchPosts();
    })

    function handleFieldChange() {
        let blockType = blockTypeField.val();
        let setting = settingField.val();
        let fieldName = `${setting}Field`;
        let optionName = `${setting}_name`;

        if (blockType && setting) {
            $.get('/wp-json/lyquix/v3/get-options', {block_type: blockType, setting: setting}, function (response) {
                fields[fieldName].html('');
                $.each(response, function (index, option) {
                    fields[fieldName].append($('<option>', {value: option[optionName], text: option[optionName]}));
                });
                if (response.length) {
                    searchPosts();
                }
            });
        }
    }

    function searchPosts() {
        let blockType = blockTypeField.val();
        let setting = settingField.val();
        let fieldName = `${setting}Field`;
        let optionValue = fields[fieldName].val();

        if (blockType && setting && optionValue) {
            if (!$('#results').length) {
                $('#post-body').append('<div id="results"></div>')
            }
            let resultsElem = $('#results');
            resultsElem.text('Loading ...');
            $.get('/wp-json/lyquix/v3/search-posts', {
                block_type: blockType,
                select: setting,
                option: optionValue
            }, function (response) {
                if (response.length > 0) {
                    resultsElem.empty();
                    $.each(response, function (index, post) {
                        resultsElem.append('<li><a href="' + post.link + '">' + post.title + '</a></li>');
                    });
                } else {
                    resultsElem.html('<li>No posts found.</li>');
                }
            });
        }
    }
});