{#
 # Copyright (C) 2017 Smart-Soft
 # All rights reserved.
 #
 # Redistribution and use in source and binary forms, with or without
 # modification, are permitted provided that the following conditions are met:
 #
 # 1. Redistributions of source code must retain the above copyright notice,
 #    this list of conditions and the following disclaimer.
 #
 # 2. Redistributions in binary form must reproduce the above copyright
 #    notice, this list of conditions and the following disclaimer in the
 #    documentation and/or other materials provided with the distribution.
 #
 # THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 # INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 # AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 # AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 # OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 # SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 # INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 # CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 # ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 # POSSIBILITY OF SUCH DAMAGE.
 #}
<script type="text/javascript">
    $( document ).ready(function() {

        /*************************************************************************************************************
         * link general actions
         *************************************************************************************************************/

        var data_get_map = {'frm_GeneralSettings':"/api/proxysso/settings/get"};

        // load initial data
        mapDataToFormUI(data_get_map).done(function(){
            formatTokenizersUI();
            $('.selectpicker').selectpicker('refresh');
        });

        var ldaps =  [
        {% for ldap in ldaps %}
        "{{ldap}}",
        {% endfor %}
        ];

        $.each(ldaps, function(index){
            // load checklist data
            updateKerberosChecklist(index);

            $("#RefreshCheckList_" + index).click(function () {
                updateKerberosChecklist(index);
            });

            $("#ShowKeytab_" + index).click(function () {
                ajaxCall(url = "/api/proxysso/service/showkeytab", sendData = {}, callback = function (data, status) {
                    $("#kerberos_output_" + index).html(data['response']);
                });
            });

            $("#DeleteKeytab_" + index).click(function () {
                ajaxCall(url = "/api/proxysso/service/deletekeytab", sendData = {}, callback = function (data, status) {
                    $("#kerberos_output_" + index).html(data['response']);
                });
            });

            $("#CreateKeytab_" + index).click(function () {
                $("#CreateKeytab_" + index).addClass("disabled");
                $("#DeleteKeytab_" + index).addClass("disabled");
                $("#ShowKeytab_" + index).addClass("disabled");
                $("#TestKerbLogin_" + index).addClass("disabled");
                $("#CreateKeytab_progress_" + index).addClass("fa fa-spinner fa-pulse");
                ajaxCall(url = "/api/proxysso/service/createkeytab", sendData = {
                    "admin_login": $("#admin_username_" + index).val(),
                    "admin_password": $("#admin_password_" + index).val(),
                    "index": index
                }, callback = function (data, status) {
                    $("#CreateKeytab_" + index).removeClass("disabled");
                    $("#DeleteKeytab_" + index).removeClass("disabled");
                    $("#ShowKeytab_" + index).removeClass("disabled");
                    $("#TestKerbLogin_" + index).removeClass("disabled");
                    $("#CreateKeytab_progress_" + index).removeClass("fa fa-spinner fa-pulse");
                    $("#kerberos_output_" + index).html(data['response']);
                });
            });

            $("#TestKerbLogin_" + index).click(function () {
                ajaxCall(url = "/api/proxysso/service/testkerblogin", sendData = {
                    "login": $("#username_" + index).val(),
                    "password": $("#password_" + index).val(),
                    "index": index
                }, callback = function (data, status) {
                    $("#kerberos_output_" + index).html(data['response']);
                });
            });
        });

        // link save button to API set action
        $("#applyAct").click(function () {
            $("#responseMsg").html('');
            $("#applyAct_progress").addClass("fa fa-spinner fa-pulse");
            $("#applyAct").addClass("disabled");
            saveFormToEndpoint(url = "/api/proxysso/settings/set", formid = 'frm_GeneralSettings', callback_ok = function () {

                ajaxCall(url = "/api/proxy/service/reconfigure", sendData = {}, callback = function (data, status) {
                    if (data.status == "ok") {
                        $("#responseMsg").html("{{ lang._('Proxy reconfigured') }}");
                        $("#responseMsg").removeClass("hidden");
                    }

                    $("#applyAct_progress").removeClass("fa fa-spinner fa-pulse");
                    $("#applyAct").removeClass("disabled");
                });
            });
        });
        // update history on tab state and implement navigation
        if (window.location.hash != "") {
            $('a[href="' + window.location.hash + '"]').click()
        }
        $('.nav-tabs a').on('shown.bs.tab', function (e) {
            history.pushState(null, null, e.target.hash);
        });
    });

    function showDump(fieldname, index)
    {
        $("#kerberos_output_" + index).html($("#" + fieldname + "_dump").html());
        $("#kerberos_output_" + index)[0].scrollIntoView(true);
    }

    function updateKerberosChecklist(ldap_index)
    {
        $("#refresh_progress_" + ldap_index).addClass("fa fa-spinner fa-pulse");
        $("#RefreshCheckList_" + ldap_index).addClass("disabled");

        var checklist_get_map = {};
        checklist_get_map['frm_CheckList_' + ldap_index] = "/api/proxysso/service/getchecklist/" + ldap_index;
        mapDataToFormUI(checklist_get_map).done(function(data){

            $("#refresh_progress_" + ldap_index).removeClass("fa fa-spinner fa-pulse");
            $("#RefreshCheckList_" + ldap_index).removeClass("disabled");

            $.each(data["frm_CheckList_" + ldap_index], function(index, value){
                
                // clear data
                $("#" + index).html("");
                $(".help-block[id*='" + index + "']").html("");

                if(value.status == "ok") {
                    jQuery('<div/>', {
                        id: index + '_indicator',
                        class: 'fa fa-check-circle text-success',
                    }).appendTo("#" + index);
                    if(value.message) {
                        $(".help-block[id*='" + index + "']").html(value.message);
                    }
                }
                else if(value.status == "failure") {
                    jQuery('<div/>', {
                        id: index + '_indicator',
                        class: 'fa fa-times-circle text-danger',
                    }).appendTo("#" + index);
                    if(value.message) {
                        $(".help-block[id*='" + index + "']").html(value.message);
                    }
                }
                else {
                    $("#" + index).html(value);
                }

                if(value.dump) {
                    jQuery('<div/>', {
                        id: index + '_dump',
                        text: htmlDecode(value.dump),
                        class: 'hidden',
                    }).appendTo(".help-block[id*='" + index + "']");
                    jQuery('<a/>', {
                        text: "{{ lang._('Show dump') }}",
                        href: 'javascript:showDump("' + index + '", ' + ldap_index + ');',
                        style: 'padding-left: 20px;',
                    }).appendTo("#" + index);
                }
            });
        });
    }

</script>

<div class="alert alert-info hidden" role="alert" id="responseMsg">
</div>

<ul class="nav nav-tabs" role="tablist"  id="maintabs">
    <li class="active"><a data-toggle="tab" href="#general"><b>{{ lang._('General') }}</b></a></li>
    {% for ldap in ldaps %}
    <li><a data-toggle="tab" href="#testing_{{loop.index0}}"><b>{{ lang._('Kerberos Authentication') }}: {{ldap}}</b></a></li>
    {% endfor %}
</ul>

<div class="tab-content content-box">

    <div class="tab-pane fade in active" id="general">
        {{ partial("layout_partials/base_form",['fields':generalForm,'id':'frm_GeneralSettings'])}}

        <hr/>
        <button class="btn btn-primary"  id="applyAct" type="button"><b>{{ lang._('Apply') }}</b> <i id="applyAct_progress" class=""></i></button>
    </div>

    {% set lp = [] %}
    {% set lp["index"] = 0 %}
    {% for ldap in ldaps %}
    <div class="tab-pane fade" id="testing_{{lp["index"]}}">

        {{ partial("layout_partials/base_form",['fields':checkListForm[lp["index"]],'id':'frm_CheckList_' ~ lp["index"]])}}
        <hr/>
        <button class="btn btn-primary" id="RefreshCheckList_{{lp["index"]}}" type="button"><b>{{ lang._('Refresh') }}</b> <i id="refresh_progress_{{lp["index"]}}" class=""></i></button>

        {{ partial("layout_partials/base_form",['fields':testingCreateForm[lp["index"]],'id':'frm_TestingCreate_' ~ lp["index"]])}}
        <button class="btn btn-primary" id="CreateKeytab_{{lp["index"]}}" type="button"><b>{{ lang._('Create keytab') }}</b> <i id="CreateKeytab_progress_{{lp["index"]}}" class=""></i></button>
        <button class="btn btn-primary" id="DeleteKeytab_{{lp["index"]}}" type="button"><b>{{ lang._('Delete keytab') }}</b></button>
        <button class="btn btn-primary" id="ShowKeytab_{{lp["index"]}}" type="button"><b>{{ lang._('Show keytab') }}</b></button>
        <br/>
        <br/>

        {{ partial("layout_partials/base_form",['fields':testingTestForm[lp["index"]],'id':'frm_TestingTest_' ~ lp["index"]])}}
        <button class="btn btn-primary" id="TestKerbLogin_{{lp["index"]}}" type="button"><b>{{ lang._('Test Keberos login') }}</b></button>
        <br/>
        <br/>

        <hr/>
        <p><b>{{ lang._('Output') }}</b></p>
        <pre id="kerberos_output_{{lp["index"]}}"></pre>
        <br/>
    </div>
        {% set lp["index"] = lp["index"] + 1 %}
    {% endfor %}
</div>
