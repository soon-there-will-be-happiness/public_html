/*jshint -W104*/

_folder_loads = [];

$(window).on('load', function() {
    $('input[type="selected_paths"]').each(function() {
        id = $(this).attr('id');
        if(typeof id == 'undefined')
            id = randString(5);

        $(this).after('<div class="files_walk" id="' + id + '"></div>');

        new FilesWalk(
            $('#' + id), 
            $(this).val(),
            $(this).parents('form'),
            id,
            $(this).attr('name')
        );

        $(this).remove();
    });
});

function FilesWalk(element, path, form, id, name){
    load(element, path, form);

    function on(temp_element){ 
        temp_element.on('click', function(){
            folder = $(this).parent();
            type = $(this).parent();
            data = type.data();
            let status = $(this).find('.status .title');

            if(data.type == 'folder'){

                if(type.find('.get_folder').length > 0){

                    type.find('.get_folder').remove();
                    status.data('show', false);

                    status.removeClass('hide');
                    status.addClass('show');
                }

                else{
                    if(_folder_loads[data.path]){
                        type.append(_folder_loads[data.path]);
                        on(type.find('.get_folder > .type > .line'));
                    }
                    
                    else
                        load(folder, data.path, form, status);
                }
            }

            else{
                input = element.find('input[name="' + name + '[' + data.path + ']"]');

                if(input.length > 0){
                    status.removeClass('del');
                    status.addClass('add');
                    input.remove();
                }

                else{
                    element.append('<input data-id="' + id + '-selected_paths" type="hidden" name="' + name + '[' + data.path + ']" value="1">');
                    status.addClass('del');
                    status.removeClass('add');
                }
            }
        });

        temp_element.each(function(){
            type = $(this).parent();
            data = type.data();

            if(data.type != 'folder'){
                let status = $(this).find('.status .title');

                input = element.find('input[name="' + name + '[' + data.path + ']"]');
                console.log(input);

                if(input.length > 0){
                    status.addClass('del');
                    status.removeClass('add');
                }
            }
        });
    }


    function load(block, path, form, status){
        $.ajax({
            type: 'POST',
            url: '/lib/FilesWalk/src/ajax.php?' + admin_token,
            dataType: 'html',
            data: {
                root: path,
                selected_paths: form.serialize(),
                name: name
            },
            success : function(data){
                block.append(data);

                if(status){
                    // _folder_loads[path] = data;
                    status.removeClass('show');
                    status.addClass('hide');
                    status.data('show', true);
                }

                on(block.find('.get_folder > .type > .line'), id, form);
            },
            error : function(XMLHttpRequest, textStatus, errorThrown) {
                console.log('error');
            }
        });
    }
}