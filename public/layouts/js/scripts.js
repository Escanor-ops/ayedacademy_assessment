window.addEventListener('scroll', () => {
    const navbar = document.querySelector(".navbar");
    if( window.scrollY >= 60 ) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }
});



function _sidebarToggle()
{
    const sidebar   = document.getElementById('sidebar');
    const rgba      = document.getElementById('rgba');
    const body      = document.querySelector('body');
    sidebar.classList.toggle('active');
    rgba.classList.toggle('active');
    body.classList.toggle('overflow-hidden');
}

function _confirm(event, message)
{
    const popup = confirm(message);
    if(!popup){
        event.preventDefault();
    }
    return false;
}
function _toggle_customer_sidebar()
{
    const customer_sidebar  = document.getElementById('customer-sidebar');
    const body              = document.getElementsByTagName('body')[0];
    const overlay           = document.getElementById('overlay');
    customer_sidebar.classList.toggle('customer_sidebar_toggled');
    overlay.classList.toggle('overlay-toggled');
    body.classList.toggle('overflow-hidden');
}
// START NAVBAR WITH CONTENTS;
function _show_navbar_content(elem)
{
    // const _id = elem.getAttribute("data-toggle");
    const _contents     = elem.querySelector('.navbar-contents'),
        _inside_links   = elem.querySelectorAll('.one-link');

    _inside_links.forEach( (item, index) => {
        item.style.opacity = 1;
        item.style.animationDelay = (index + 1) * (15/100)  + 's';
        item.classList.add('animated', 'fadeInDown');
    });
    _contents.classList.add('active')
}
function _hide_navbar_content(elem)
{
    const _contents     = elem.querySelector('.navbar-contents'),
    _inside_links   = elem.querySelectorAll('.one-link');

    _inside_links.forEach( (item, index) => {
        item.classList.remove('animated', 'fadeInDown');
    });
    _contents.classList.remove('active')
}
// END NAVBAR WITH CONTENTS;
function _upload_files(elem, recipter, types_allowd, _label, _check_count = null)
{
    if(_check_count != null){
        if(elem.files.length > _check_count){
            alert(`برجاء رفع ${_check_count} كحد أقصى`);
            return false;
        }
    }
    if(elem.files && elem.files[0]){
        let file_type = elem.files[0].name.split(".").pop().toLowerCase();
        if(types_allowd.includes(file_type)){
            document.querySelector(_label).classList.add('label-success');
            if(recipter == false) return false;
            const block = document.querySelector(recipter);
            const reader = new FileReader();
            reader.readAsDataURL(elem.files[0]);
            reader.onload = () => {
                block.src = reader.result;
            };
        }else{
            alert("برجاء رفع ملف مسموح به");
            return false;
        }
    }
}

function _toggle_sidebar_orders(rgba_type = null)
{
    rgba_type = rgba_type == 'class' ? '.overlay' : '#overlay' ;
    let sidebar = document.querySelector('.sidebar-order'),
        rgba    = document.querySelector(rgba_type);
    sidebar.classList.toggle('sidebar-orders-toggle');
    rgba.classList.toggle('overlay-toggled');
}

function _toggle_tap(elem)
{
    let taps = document.querySelectorAll('.card-inserter');
    if(elem.getAttribute('data-toggle') == 'closed'){
        taps.forEach( (tap) => {
            tap.classList.remove('tap-closed');
        });
        elem.setAttribute('data-toggle', 'opend');
        elem.querySelector('span').classList.remove('fa-plus');
        elem.querySelector('span').classList.add('fa-times');
    }else{
        taps.forEach( (tap) => {
            tap.classList.add('tap-closed');
        });
        elem.setAttribute('data-toggle', 'closed');
        elem.querySelector('span').classList.remove('fa-times');
        elem.querySelector('span').classList.add('fa-plus');
    }
}

function _submitTaps(id, duration)
{
    const submit = document.getElementById(id);
    const loader = document.getElementById('loader-tap');
    loader.style.setProperty('--animation-status', "running")
    setTimeout(() => {
        submit.click();
    }, duration);
}
function _alert(text, clss, duration = 5000)
{
    Toastify({
        text: text,
        className: clss,
        duration: duration,
        newWindow: true,
        close: false,
        gravity: 'top',
        position: 'left',
        stopOnFocus: true,
        style: {
            background: 'linear-gradient(to right, #00b09b, #96c93d)',
        },
    }).showToast();
}

const all_inputs = document.querySelectorAll('.add-taps input, .add-taps select');
all_inputs.forEach( (input) => {
    let _action = input.tagName == "INPUT" || input.tagName == "TEXTAREA" ? 'keyup' : 'change' ;
    input.addEventListener(_action, () => {
        if(input.hasAttribute('required')) {
            if(input.getAttribute('type') === "email") {
                const _regexForEmail = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
                if( input.value.match(_regexForEmail) ) {
                    input.style.borderColor = 'green';
                    input.classList.remove('errorAnimation');
                    // continue;
                    return;
                }
            } else {
                if(input.value.trim() != '') {
                    input.style.borderColor = 'green';
                    input.classList.remove('errorAnimation');
                    return;
                }
            }
            input.style.borderColor = 'red';
            input.classList.add('errorAnimation');
        }
    });
});

function _input_error(input)
{
    input.style.borderColor = 'red';
    input.classList.add('errorAnimation');
}

function _validations(inputs, fillters)
{
    let _errors = false;
    inputs.forEach( (input) => {
        if(input.hasAttribute('required')) {
            if(input.getAttribute('type') === "email") {
                const _regexForEmail = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
                if( !input.value.match(_regexForEmail) ) {
                    _input_error( input );  
                    _errors = true; 
                }
            } else if(input.getAttribute('type') === "radio"){
                return false;
            }else {
                if(input.value.trim() == '') {
                    _input_error( input );  
                    _errors = true; 
                }
            }
        } 
    });
    return _errors;
}

function _openTap(id, last_tap = false, submit_id, timeout = 6000, elem = null)
{   
    const _parent   = elem.parentElement.parentElement;
    const inputs    = _parent.querySelectorAll('input, select, textarea');

    if( _validations(inputs) === true ) {
        _alert("برجاء إدخال جميع الحقول المطلوبة قبل الإنتقال للخطوة التالية.", "error", 2500);
        return false;
    }

    if(last_tap == true) _submitTaps(submit_id, timeout);
    document.getElementById(id).classList.add('active-tap');
}

function _returnTap(elem)
{
    elem.parentElement.classList.remove('active-tap');
}
function _clicked(elem, type = null, clss = null)
{
    if(type == 'radio'){
        const divs = document.querySelectorAll('.'+clss);
        divs.forEach( (item) => {
            item.nextElementSibling.classList.remove('active');
        });
    }
    elem.nextElementSibling.classList.toggle('active')
}
function _clicked_button(elem, clss = null)
{
    const divs = document.querySelectorAll('.'+clss);
    divs.forEach( (item) => {
        item.classList.remove('active');
    });
    elem.classList.toggle('active');
}
function _toggleAddTaps(id = null)
{
    if(id != null) document.getElementById(id).style.display = 'block';
    const taps = document.querySelector('.add-taps').classList.toggle('show');
}
function _addImage()
{
    const block = document.getElementById('addImages');
    const branches_count = block.querySelectorAll('.col-12.main');
    const col = document.createElement('div');
    col.className = 'col-12 mb-3 main';
    col.innerHTML = 
    `
        <button type="button" onclick="_accord(this)" class="accordion d-flex justify-content-between align-items-center p-3 rounded-4">
            <div>صورة (${branches_count.length + 1})</div>
            <div class="d-flex align-items-center">
                <span class="rounded-3 fa fa-trash ms-4 fs-7" onclick="_removeBranches(this)" style="background-color:#b31818;color:#fff;padding:6px;"></span>
                <span class="fa fa-arrow-left"></span>
            </div>
        </button>
        <div class="accordion-panel rounded-4">
            <div class="row">
                <div class="col-12 mt-3">
                    <label for="image_${branches_count.length + 1}" id="image_label_${branches_count.length + 1}" class="uploadFileND p-5 text-center w-100 rounded-4">
                        <span class="fa fa-cloud-arrow-up mb-3"></span>
                        <div class="fw-bold">ارفع الصورة</div>
                    </label>
                    <input type="file" id="image_${branches_count.length + 1}" name="images[]" onchange="_upload_files(this, '', '[png, jpg, jpeg, webp]', '#image_label_${branches_count.length + 1}')" accept="image/png, image/jpg, image/jpeg, image/webp" class="mt-1 form-control d-none" >
                    <div class="alert alert-warning rounded-4 shadow-sm mt-3 fs-7"> الملفات المسموح برفعها هي: <br>  (jpeg - webp - png - jpeg) <br> علما أن حجم الملف يجب أن لا يتجاوز <span class="fw-bold me-1"> 1MB </span></div>
                </div>
            </div>
        </div>
    `;
    block.appendChild(col);
}
function _removeBranches(elem)
{
    elem.parentElement.parentElement.parentElement.remove();
}
function _accord(elem){
    elem.classList.toggle("active");
    let panel = elem.nextElementSibling;
    if (panel.style.display === "block") {
        panel.style.display = "none";
    } else {
        panel.style.display = "block";
    }
}
function _toggle_sidebar_notifications()
{
    let sidebar = document.querySelector('.sidebar-notifications'),
        rgba    = document.querySelector(".overlay-nots");
    sidebar.classList.toggle('sidebar-orders-toggle');
    rgba.classList.toggle('overlay-toggled');
}
function _toggleSearchTap(elem)
{
    elem.classList.toggle('btn-danger');
    elem.classList.toggle('btn-primary');
    elem.querySelector('span').classList.toggle('fa-times');
    document.getElementById('search-panel').classList.toggle('d-none');
}
function _setSearchType(elem, table, type, hint = 'ابحث...'){
    table_search = table;
    type_search = type;
    const input = document.getElementById('search');
    input.setAttribute('placeholder', hint); 
    input.removeAttribute('readonly');
    _clicked_button(elem, 'search_by');
}
function _exportTableToExcel(tableID, filename = ''){
    let downloadLink;
    let dataType = 'application/vnd.ms-excel';
    let tableSelect = document.getElementById(tableID);
    let tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    filename = filename ? filename + '.xls' : 'excel_data.xls';
    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);
    if(navigator.msSaveOrOpenBlob){
        let blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename );
    }else{
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}
function _remove_issue_file(elem, file)
{
    const input = document.getElementById('input_issue_files');
    let files = input.value.split(',');
    files.forEach( (item, index) => {
        if( item === file ) files.splice(index, 1);
    });
    input.value = files.join();
    console.log(files)
    elem.parentElement.remove();
}