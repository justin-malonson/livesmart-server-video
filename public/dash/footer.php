<div id="chats-lsv-admin"></div>
<script>
    var copyUrl = function (url, notify) {
        var aux = document.createElement("input");
        aux.setAttribute("value", url);
        document.body.appendChild(aux);
        aux.select();
        document.execCommand("copy");
        document.body.removeChild(aux);
        if (notify) {
            $('#infoModalLabelAgent').hide();
            $('#infoModalLabelVisitor').hide();
            $('#' + notify).show();
            $('#infoModal').modal('toggle');
            setTimeout(function () {
                $('#infoModal').modal('hide');
            }, 3000);
        }
    };

    var deleteItem = function (itemid, type, event) {
        event.preventDefault()
        if (type === 'room') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deleteroom', 'agentId': agentId, 'roomId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        } else if (type === 'agent') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deleteagent', 'agentId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        } else if (type === 'user') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deleteuser', 'userId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        } else if (type === 'recording') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deleterecording', 'recordingId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        } else if (type === 'plan') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deleteplan', 'planId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        } else if (type === 'subscription') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deletesubscription', 'subscriptionId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        }
    };

    var getCurrentDateFormatted = function (date, format) {
        if (!format) {
            format = 'isoDate'
        }
        var currentdate = new Date(date);
        if (currentdate.getDate()) {
            return currentdate.format(format);

        } else {
            return '';
        }
    };

    var dateFormat = function () {
            var	token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
                timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
                timezoneClip = /[^-+\dA-Z]/g,
                pad = function (val, len) {
                    val = String(val);
                    len = len || 2;
                    while (val.length < len) val = "0" + val;
                    return val;
                };

            // Regexes and supporting functions are cached through closure
            return function (date, mask, utc, i18n) {
                var dF = dateFormat;

                // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
                if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
                    mask = date;
                    date = undefined;
                }

                // Passing date through Date applies Date.parse, if necessary
                date = date ? new Date(date) : new Date;
                if (isNaN(date))
                    throw SyntaxError("invalid date");

                mask = String(dF.masks[mask] || mask || dF.masks["default"]);

                // Allow setting the utc argument via the mask
                if (mask.slice(0, 4) == "UTC:") {
                    mask = mask.slice(4);
                    utc = true;
                }

                var _ = utc ? "getUTC" : "get",
                        d = date[_ + "Date"](),
                        D = date[_ + "Day"](),
                        m = date[_ + "Month"](),
                        y = date[_ + "FullYear"](),
                        H = date[_ + "Hours"](),
                        M = date[_ + "Minutes"](),
                        s = date[_ + "Seconds"](),
                        L = date[_ + "Milliseconds"](),
                        o = utc ? 0 : date.getTimezoneOffset(),
                        flags = {
                            d: d,
                            dd: pad(d),
                            ddd: i18n.dayNames[D],
                            dddd: i18n.dayNames[D + 7],
                            m: m + 1,
                            mm: pad(m + 1),
                            mmm: i18n.monthNames[m],
                            mmmm: i18n.monthNames[m + 12],
                            yy: String(y).slice(2),
                            yyyy: y,
                            h: H % 12 || 12,
                            hh: pad(H % 12 || 12),
                            H: H,
                            HH: pad(H),
                            M: M,
                            MM: pad(M),
                            s: s,
                            ss: pad(s),
                            l: pad(L, 3),
                            L: pad(L > 99 ? Math.round(L / 10) : L),
                            t: H < 12 ? "a" : "p",
                            tt: H < 12 ? "am" : "pm",
                            T: H < 12 ? "A" : "P",
                            TT: H < 12 ? "AM" : "PM",
                            Z: utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                            o: (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                            S: ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
                        };

                return mask.replace(token, function ($0) {
                    return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
                });
            };
        }();

        dateFormat.masks = {
            "default": "dd-mm-yyyy HH:MM",
            shortDate: "m/d/yy HH:MM",
            mediumDate: "mmm d, yyyy HH:MM",
            longDate: "mmmm d, yyyy HH:MM",
            fullDate: "dddd, mmmm d, yyyy HH:MM",
            isoDate: "yyyy-mm-dd HH:MM",
            isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
        };

        Date.prototype.format = function (mask, utc) {
            let i18n = {
                dayNames: [
                    "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
                    "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
                ],
                monthNames: [
                    "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
                    "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
                ]
            };
            return dateFormat(this, mask, utc, i18n);
        };
    var isAdmin = true;
    var roomId = false;
<?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>
        var agentId = false;
<?php } else { ?>
        var agentId = "<?php echo @$_SESSION["tenant"]; ?>";
<?php } ?>
</script>



</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->
<?php
if ($isInclude) {
    ?>
    <!-- Footer -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright &copy; LiveSmart Server Video <?php echo date('Y'); ?></span>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->
    <?php
}
?>
</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="requestModal" tabindex="-1" role="dialog" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="request_session" data-localize="request_session"></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="openUrl" data-localize="click_to_open"></button>
                <button class="btn btn-secondary" type="button" id="closeUrl" data-localize="click_to_cancel"></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoModalLabelAgent" data-localize="confAgentUrl"></h5>
                <h5 class="modal-title" id="infoModalLabelVisitor" data-localize="confVisitorUrl"></h5>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal" data-localize="ok"></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel" data-localize="ready_leave"></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" data-localize="select_logout"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal" data-localize="cancel"></button>
                <a class="btn btn-primary" href="logout.php" data-localize="logout"></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="generateLinkModal" tabindex="-1" role="dialog" aria-labelledby="generateLinkModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel" data-localize="video_attendee_url"></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" data-localize="video_attendee_info"></div>
            <div class="modal-footer">
                <button class="btn btn-primary mr-auto" type="button" id="copyAttendeeUrl" data-localize="copy_url"></button>
                <button class="btn btn-secondary" type="button" data-dismiss="modal" data-localize="close"></button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>
<script src="js/detect.js"></script>
<script>
    let visitorUrl, agentUrl;
    function generateLink(room, id, open, password) {
        const hrefUrl = new URL(window.location.href);
        if (agentId) {
            room.agentId = agentId;
        } else {
            <?php if (@$_SESSION["tenant"] == 'lsv_mastertenant' && @$_SESSION['agent']['payment_enabled']) { ?>
                room.agentId = '<?php echo @$_SESSION['agent']['tenant']; ?>';
            <?php } ?>
        }
        let roomObject = (Object.keys(room).length > 0) ? '?p=' + window.btoa(unescape(encodeURIComponent(JSON.stringify(room)))) : ''
        visitorUrl = hrefUrl.protocol + '//' + hrefUrl.host + '/' + id + roomObject;
        room.admin = 1;
        if (password) {
            room.pass = password;
        }
        agentUrl = hrefUrl.protocol + '//' + hrefUrl.host + '/' + id + '?p=' + window.btoa(unescape(encodeURIComponent(JSON.stringify(room))));
        if (open) {
            copyUrl(visitorUrl);
            window.open(agentUrl);
            var text = $('#generateLinkModal').html();
            $('#generateLinkModal').html(text.replace('[generateLink]', visitorUrl));
            $('#generateLinkModal').modal('toggle');
            $('#copyAttendeeUrl').off();
            $('#copyAttendeeUrl').on('click', function () {
                $('#generateLinkModal').modal('hide');
            });
        }
    }

    $('#generateLink').on('click', function () {
        let random = Math.random().toString(36).slice(2).substring(0, 10);
        let roomObject = {};
        var fileConfig = '<?php echo @$_GET['file'];?>';
        if ($('#roomName').val()) {
            random = $('#roomName').val();
        }
        if ($('#names').val()) {
            roomObject.agentName = $('#names').val();
        }
        if ($('#visitorName').val()) {
            roomObject.visitorName = $('#visitorName').val();
        }
        if ($('#datetime').val()) {
            let datetime = new Date($('#datetime').val()).toISOString();
            roomObject.datetime = datetime;
        }
        if ($('#config').val()) {
            roomObject.config = $('#config').val().replace('.json', '');
        }
        if (fileConfig) {
            roomObject.config = fileConfig;
        }
        if ($('#duration').val() || $('#durationtext').val()) {
            let duration = ($('#durationtext').val()) ? $('#durationtext').val() : $('#duration').val();
            roomObject.duration = duration;
        }
        if ($('#disableVideo').prop('checked')) {
            roomObject.disableVideo = true;
        }
        if ($('#disableAll').prop('checked')) {
            roomObject.disableAll = true;
        }
        let pass = ($('#password').val()) ? $('#password').val() : '';
        generateLink(roomObject, random, true, pass);
    });


    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

<?php
if (isset($_SESSION["username"]) && isset($_SESSION["agent"]["tenant"])) { ?>
<script src="/socket.io/socket.io.js"></script>

<script>
    const socket = io();
    const room_id = '<?php echo $_SESSION["agent"]["tenant"];?>'
    socket.request = function request(type, data = {}) {
        return new Promise((resolve, reject) => {
            socket.emit(type, data, (data) => {
                if (data.error) {
                    reject(data.error);
                } else {
                    resolve(data);
                }
            });
        });
    };
    socket.on(
        'connect',
        function () {
            socket
                .request('createRoom', {
                    room_id,
                })
                .then(
                    function (room) {
                        let data = {
                            room_id: room.id,
                            peer_info: {peer_admin: 1, peer_name: '<?php echo @$_SESSION["agent"]["tenant"];?>'}
                        };
                        socket
                            .request('join', data)
                            .then(
                                async function (room) {
                                    //console.log(room);
                                }.bind(this),
                            )
                            .catch((err) => {
                                console.error('Join error:', err);
                            });
                    }.bind(this),
                )
                .catch((err) => {
                    console.error('Create room error:', err);
                });
        }.bind(this),
        socket.on(
            'requestSession',
            function (data) {
                $('#requestModal').modal('toggle');
                $('#request_session').text($('#request_session').text().replace('{{visitor}}', data.peer_name));
                const hrefUrl = new URL(window.location.href);
                let roomObject = {};
                roomObject.admin = 1;
                roomObject.config = data.room_id;
                agentUrl = hrefUrl.protocol + '//' + hrefUrl.host + '/' + data.room_id + '?p=' + window.btoa(unescape(encodeURIComponent(JSON.stringify(roomObject))));
                $(document).on('click', '#openUrl', function() {
                    window.open(agentUrl, '_blank');
                    $('#requestModal').modal('hide');
                    socket
                    .request('exitRoom')
                    .catch((err) => {
                        console.error('Create room error:', err);
                    });
                });
                $(document).on('click', '#closeUrl', function() {
                        $('#requestModal').modal('hide');
                        socket
                        .request('exitRoomAll')
                        .catch((err) => {
                            console.error('Create room error:', err);
                        });
                    });
            }.bind(this),
        )
    );
</script>
<?php } ?>


<?php if ($basename == 'agent.php') { ?>


    <script>

    <?php
    if (isset($_GET['id'])) {
        ?>
            $('#usernameDiv').hide();
        <?php
    } else {
        ?>
            $('#usernameDiv').show();
        <?php
    }
    ?>
        jQuery(document).ready(function ($) {
            $('#error').hide();
            $('#saveAgent').click(function (event) {

                var regex = /^[\w.]+$/i
                var isValid = regex.test($('#tenant').val());
                if (!isValid) {
                    $('#error').show();
                    $('#error').html('<span data-localize="error_tenant_save"></span>');
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                    return false;
                }

    <?php
    if (isset($_GET['id'])) {
        ?>
                    var dataObj = {'type': 'editagent', 'agentId': <?php echo $_GET['id']; ?>, 'firstName': $('#first_name').val(), 'lastName': $('#last_name').val(), 'tenant': $('#tenant').val(), 'email': $('#email').val(), 'password': $('#password').val(), 'usernamehidden': $('#usernamehidden').val(), 'is_master': $('#is_master').prop('checked')};
        <?php
    } else {
        ?>
                    var dataObj = {'type': 'addagent', 'username': $('#username').val(), 'firstName': $('#first_name').val(), 'lastName': $('#last_name').val(), 'tenant': $('#tenant').val(), 'email': $('#email').val(), 'password': $('#password').val(), 'is_master': $('#is_master').prop('checked')};
        <?php
    }
    ?>
                $.ajax({
                    type: 'POST',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                <?php if ($_SESSION["tenant"] == 'lsv_mastertenant' || $_SESSION['tenant_admin']) { ?>
                                    location.href = 'agents.php';
                                <?php } else { ?>
                                    location.href = 'dash.php';
                                <?php } ?>
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_agent_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function () {
                        });
            });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getadmin', 'id': <?php echo (int) @$_GET['id'] ?>}
            })
                    .done(function (data) {
                        if (data) {
                            data = JSON.parse(data);
                            $('#agentTitle').html(data.first_name + ' ' + data.last_name);
                            $('#usernamehidden').val(data.username);
                            $('#username').val(data.username);
                            if (data.password) {
                                $('#leftblank').html(' <span data-localize="left_blank_changed"></span>');
                            }
                            //$('#password').val(data.password);
                            $('#first_name').val(data.first_name);
                            $('#last_name').val(data.last_name);
                            $('#tenant').val(data.tenant);
                            $('#email').val(data.email);
                            $('#is_master').prop('checked', data.is_master);
                            var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                            $('[data-localize]').localize('dashboard', opts);
                        }
                    })
                    .fail(function (e) {
                        console.log(e);
                    });
        });</script>

    <?php
}

if ($basename == 'recordings.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {

            $(document).on('click', '.deleteRecordingRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.recording_id, 'recording', e);
            });

            var dataTable = $('#recordings_table').DataTable({
                "pagingType": "numbers",
                "order": [[3, 'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getrecordings'}
                },
                "columns": [

                    {
                        "data": "filename",
                        "name": "filename",
                        render: function (data, type) {
                            return '<a href="../server/recordings/' + data + '" target="_blank">' + data + '</a>';
                        }
                    },
                    {
                        "data": "room_id",
                        "name": "room_id",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "agent_id",
                        "name": "agent_id",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "date_created",
                        "name": "date_created",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "recording_id",
                        "orderable": false,
                        render: function (data, type, row) {
                            var link = '<a href="../server/recordings/' + row.filename + '" target="_blank" data-localize="view"></a> | <a href="#" class="deleteRecordingRow" data-localize="delete"></a>';
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });

        });</script>

    <?php
}

if ($basename == 'config.php') {
    ?>

    <script>


        jQuery(document).ready(function ($) {
            $('#error').hide();
            let voices = [];
            window.speechSynthesis.onvoiceschanged = () => {
                voices = window.speechSynthesis.getVoices();
                let voiceSelect = document.querySelector("#text_speech_lang");
                voices.forEach((voice, i) => (voiceSelect.options[i] = new Option(`${voice.lang} (${voice.name})`, voice.lang, i)));
            };
            $('#videoScreen_exitMeetingDrop').on('change', function () {
                if (this.value == 3) {
                    $('#videoScreen_exitMeeting').show();
                } else {
                    $('#videoScreen_exitMeeting').hide();
                }
            });
            $('#saveConfig').click(function (event) {
                if ($('#videoScreen_exitMeetingDrop').val() == 1) {
                    var exitMeeting = false;
                } else if ($('#videoScreen_exitMeetingDrop').val() == 2) {
                    exitMeeting = '/';
                } else if ($('#videoScreen_exitMeetingDrop').val() == 3) {
                    exitMeeting = $('#videoScreen_exitMeeting').val()
                }
                var dataObj = {'type': 'updateconfig', 'fileName': '<?php echo $fileConfig; ?>', 'data': {
                        'agentName': $('#agentName').val(),
                        'smartVideoLanguage': $('#smartVideoLanguage').val(),
                        'recording.enabled': $('#recording_enabled').prop('checked'),
                        'recording.screen': $('#recording_screen').prop('checked'),
                        'recording.saveServer': $('#recording_saveServer').prop('checked'),
                        'recording.autoStart': $('#recording_autoStart').prop('checked'),
                        'recording.filename': $('#recording_filename').val(),
                        'recording.download': $('#recording_download').prop('checked'),
                        'whiteboard.enabled': $('#whiteboard_enabled').prop('checked'),
                        'sharevideo.enabled': $('#sharevideo_enabled').prop('checked'),
                        'videoScreen.localFeedMirrored': $('#videoScreen_localFeedMirrored').prop('checked'),
                        'videoScreen.dateFormat': $('#videoScreen_dateFormat').val(),
                        'videoScreen.exitMeetingOnTime': $('#videoScreen_exitMeetingOnTime').prop('checked'),
                        'videoScreen.exitMeetingOnTimeAgent': $('#videoScreen_exitMeetingOnTimeAgent').prop('checked'),
                        'videoScreen.meetingTimer': $('#videoScreen_meetingTimer').prop('checked'),
                        'videoScreen.disableVideoAudio': $('#videoScreen_disableVideoAudio').prop('checked'),
                        'videoScreen.disableVideo': $('#videoScreen_disableVideo').prop('checked'),
                        'videoScreen.disableAttendeeVideosOff': $('#videoScreen_disableAttendeeVideosOff').prop('checked'),
                        'videoScreen.hostOnlyAccess': $('#videoScreen_hostOnlyAccess').prop('checked'),
                        'videoScreen.admit': $('#videoScreen_admit').prop('checked'),
                        'videoScreen.lockedFeed': $('#videoScreen_lockedFeed').prop('checked'),
                        'videoScreen.getSnapshot': $('#videoScreen_getSnapshot').prop('checked'),
                        'videoScreen.getReaction': $('#videoScreen_getReaction').prop('checked'),
                        'videoScreen.breakout': $('#videoScreen_breakout').prop('checked'),
                        'videoScreen.terms': $('#videoScreen_terms').val(),
                        'videoScreen.greenRoom': $('#videoScreen_greenRoom').prop('checked'),
                        'videoScreen.videoConstraint': ($('#videoScreen_videoConstraint').val()) ? JSON.parse($('#videoScreen_videoConstraint').val()) : '{"width": {"min": 640, "ideal": 1920, "max": 3840}, "height": {"min": 480, "ideal": 1080, "max": 2160}, "aspectRatio": 1.777, "frameRate": {"min": 5, "ideal": 15, "max": 30}}',
                        'videoScreen.audioConstraint': ($('#videoScreen_audioConstraint').val()) ? JSON.parse($('#videoScreen_audioConstraint').val()) : '{"echoCancellation": true, "noiseSuppression": true, "sampleRate": 44100}',
                        'videoScreen.screenConstraint': ($('#videoScreen_screenConstraint').val()) ? JSON.parse($('#videoScreen_screenConstraint').val()) : '{"frameRate": {"min": 5, "ideal": 15, "max": 30}}',
                        'serverSide.chatHistory': $('#serverSide_chatHistory').prop('checked'),
                        'serverSide.loginForm': $('#serverSide_loginForm').prop('checked'),
                        'serverSide.checkRoom': $('#serverSide_checkRoom').prop('checked'),
                        'transcribe.enabled': $('#transcribe_enabled').prop('checked'),
                        'transcribe.voiceCommands': $('#transcribe_voiceCommands').prop('checked'),
                        'transcribe.languageTo': $('#transcribe_languageTo').val(),
                        'transcribe.language': $('#transcribe_language').val(),
                        'transcribe.apiKey': $('#transcribe_apiKey').val(),
                        'transcribe.textSpeechLang': $('#text_speech_lang').val(),
                        'transcribe.textSpeechChat': $('#text_speech_chat').prop('checked'),
                        'transcribe.textSpeechTranscribe': $('#text_speech_transcribe').prop('checked'),
                        'virtualBackground.blur': $('#virtual_blur').prop('checked'),
                        'virtualBackground.backgrounds': $('#virtual_backgrounds').prop('checked'),
                        'videoScreen.enable_chat_gpt': $('#enable_chat_gpt').prop('checked'),
                        'entryForm.visitorName_enabled': $('#visitorName_enabled').prop('checked'),
                        'serverSide.payment_enabled': $('#payment_enabled').prop('checked'),
                        'videoScreen.exitMeeting': exitMeeting,
                        'metaTitle': $('#metaTitle').val(),
                    }};
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'config.php?file=<?php echo $fileConfig; ?>';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_config_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });

            $('#addConfig').click(function (event) {
                var dataObj = {'type': 'addconfig', 'fileName': $('#fileName').val()};
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'config.php?file=' + $('#fileName').val();
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_config_add"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });

    <?php
    $jsonString = file_get_contents('../config/' . $fileConfig . '.json');
    $data = json_decode($jsonString);
    ?>

            $('#agentName').val('<?php echo @$data->agentName; ?>');
            $('#metaTitle').val('<?php echo @$data->metaTitle; ?>');
            $('#smartVideoLanguage').val('<?php echo @$data->smartVideoLanguage; ?>');
            $('#videoScreen_localFeedMirrored').prop('checked', <?php echo @$data->videoScreen->localFeedMirrored; ?>);
            $('#recording_enabled').prop('checked', <?php echo @$data->recording->enabled; ?>);
            $('#recording_screen').prop('checked', <?php echo @$data->recording->screen; ?>);
            $('#recording_saveServer').prop('checked', <?php echo @$data->recording->saveServer; ?>);
            $('#recording_autoStart').prop('checked', <?php echo @$data->recording->autoStart; ?>);
            $('#recording_filename').val('<?php echo @$data->recording->filename; ?>');
            $('#recording_download').prop('checked', <?php echo @$data->recording->download; ?>);
            $('#whiteboard_enabled').prop('checked', <?php echo @$data->whiteboard->enabled; ?>);
            $('#sharevideo_enabled').prop('checked', <?php echo @$data->sharevideo->enabled; ?>);
            $('#videoScreen_dateFormat').val('<?php echo @$data->videoScreen->dateFormat; ?>');
            $('#videoScreen_exitMeetingOnTime').prop('checked', <?php echo @$data->videoScreen->exitMeetingOnTime; ?>);
            $('#videoScreen_exitMeetingOnTimeAgent').prop('checked', <?php echo @$data->videoScreen->exitMeetingOnTimeAgent; ?>);
            $('#videoScreen_meetingTimer').prop('checked', <?php echo @$data->videoScreen->meetingTimer; ?>);
            $('#videoScreen_disableVideoAudio').prop('checked', <?php echo @$data->videoScreen->disableVideoAudio; ?>);
            $('#videoScreen_disableVideo').prop('checked', <?php echo @$data->videoScreen->disableVideo; ?>);
            $('#videoScreen_disableAttendeeVideosOff').prop('checked', <?php echo @$data->videoScreen->disableAttendeeVideosOff; ?>);
            $('#videoScreen_hostOnlyAccess').prop('checked', <?php echo @$data->videoScreen->hostOnlyAccess; ?>);
            $('#videoScreen_admit').prop('checked', <?php echo @$data->videoScreen->admit; ?>);
            $('#videoScreen_lockedFeed').prop('checked', <?php echo @$data->videoScreen->lockedFeed; ?>);
            $('#videoScreen_getSnapshot').prop('checked', <?php echo @$data->videoScreen->getSnapshot; ?>);
            $('#videoScreen_getReaction').prop('checked', <?php echo @$data->videoScreen->getReaction; ?>);
            $('#videoScreen_breakout').prop('checked', <?php echo @$data->videoScreen->breakout; ?>);
            $('#videoScreen_terms').val('<?php echo @$data->videoScreen->terms; ?>');
            $('#videoScreen_greenRoom').prop('checked', <?php echo @$data->videoScreen->greenRoom; ?>);
            $('#serverSide_chatHistory').prop('checked', <?php echo @$data->serverSide->chatHistory; ?>);
            $('#serverSide_loginForm').prop('checked', <?php echo @$data->serverSide->loginForm; ?>);
            $('#serverSide_checkRoom').prop('checked', <?php echo @$data->serverSide->checkRoom; ?>);
            $('#transcribe_enabled').prop('checked', <?php echo @$data->transcribe->enabled; ?>);
            $('#transcribe_voiceCommands').prop('checked', <?php echo @$data->transcribe->voiceCommands; ?>);
            $('#transcribe_language').val('<?php echo @$data->transcribe->language; ?>');
            $('#transcribe_languageTo').val('<?php echo @$data->transcribe->languageTo; ?>');
            $('#transcribe_apiKey').val('<?php echo @$data->transcribe->apiKey; ?>');
            $('#text_speech_chat').prop('checked', <?php echo @$data->transcribe->textSpeechChat; ?>);
            $('#text_speech_transcribe').prop('checked', <?php echo @$data->transcribe->textSpeechTranscribe; ?>);
            $('#videoScreen_videoConstraint').val('<?php echo (isset($data->videoScreen->videoConstraint)) ? json_encode($data->videoScreen->videoConstraint, JSON_FORCE_OBJECT) : '{"width": {"min": 640, "ideal": 1920, "max": 3840}, "height": {"min": 480, "ideal": 1080, "max": 2160}, "aspectRatio": 1.777, "frameRate": {"min": 5, "ideal": 15, "max": 30}}'; ?>');
            $('#videoScreen_audioConstraint').val('<?php echo (isset($data->videoScreen->audioConstraint)) ? json_encode($data->videoScreen->audioConstraint, JSON_FORCE_OBJECT) : '{"echoCancellation": true, "noiseSuppression": true, "sampleRate": 44100}'; ?>');
            $('#videoScreen_screenConstraint').val('<?php echo (isset($data->videoScreen->screenConstraint)) ? json_encode($data->videoScreen->screenConstraint, JSON_FORCE_OBJECT) : '{"frameRate": {"min": 5, "ideal": 15, "max": 30}}'; ?>');
            $('#virtual_blur').prop('checked', <?php echo @$data->virtualBackground->blur; ?>);
            $('#virtual_backgrounds').prop('checked', <?php echo @$data->virtualBackground->backgrounds; ?>);
            $('#enable_chat_gpt').prop('checked', <?php echo @$data->videoScreen->enable_chat_gpt; ?>);
            $('#visitorName_enabled').prop('checked', <?php echo @$data->entryForm->visitorName_enabled; ?>);
            $('#payment_enabled').prop('checked', <?php echo @$data->serverSide->payment_enabled; ?>);
            var exitMeeting = '<?php echo addslashes($data->videoScreen->exitMeeting); ?>';
            if (exitMeeting == false) {
                $('#videoScreen_exitMeetingDrop').val(1);
                $('#videoScreen_exitMeeting').hide();
            } else if (exitMeeting == '/') {
                $('#videoScreen_exitMeetingDrop').val(2);
                $('#videoScreen_exitMeeting').hide();
            } else {
                $('#videoScreen_exitMeetingDrop').val(3);
                $('#videoScreen_exitMeeting').show();
                $('#videoScreen_exitMeeting').val(exitMeeting);
            }
            setTimeout(function () {
                $('#text_speech_lang').val('<?php echo @$data->transcribe->textSpeechLang; ?>');
            }, 300);
        });</script>

    <?php
}

if ($basename == 'locale.php') {
    ?>


    <script>

    <?php
    $jsonString = file_get_contents('../locales/en_US.json');
    $jsonStringToCompare = file_get_contents('../locales/' . $fileLocale . '.json');

    $data = json_decode($jsonString, true);
    $dataToCompare = json_decode($jsonStringToCompare, true);
    $fileContent = '';
    $fileData = '';
    foreach ($data as $key => $value) {
        $fileContent .= '<div class="form-group"><label for="roomName"><h6>' . $key . ':</h6></label><input type="text" class="form-control" id="' . $key . '" aria-describedby="' . $key . '" value="' . htmlentities(addslashes($dataToCompare[$key])) . '"></div>';
        $fileData .= "'" . $key . "': $('#" . $key . "').val(),";
    };
    $fileData = substr($fileData, 0, -1);
    ?>
        jQuery(document).ready(function ($) {
            $('#error').hide();
            $('#saveLocale').click(function (event) {
                var dataObj = {'type': 'updatelocale', 'fileName': '<?php echo $fileLocale; ?>', 'data': {<?php echo $fileData; ?>}};
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'locale.php?file=<?php echo $fileLocale; ?>';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_locale_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });
            $('#addLocale').click(function (event) {
                var dataObj = {'type': 'addlocale', 'fileName': $('#fileName').val()};
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'locale.php?file=' + $('#fileName').val();
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_locale_add"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });

            $('#localeStrings').html('<?php echo $fileContent; ?>');
        });</script>

    <?php
}

if ($basename == 'agents.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {

            $(document).on('click', '.deleteAgentRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.agent_id, 'agent', e);
            });

            var dataTable = $('#agents_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'asc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getagents'}
                },
                "columns": [
                    {
                        "data": "username",
                        "name": "username",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "first_name",
                        "name": "first_name",
                        render: function (data, type, row) {
                            return row.first_name + ' ' + row.last_name;
                        }
                    },
                    {
                        "data": "tenant",
                        "data": "tenant",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "email",
                        "name": "email",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "is_master",
                        "name": "is_master",
                        "orderable": false,
                        render: function (data, type) {
                            var yesNo = (data) ? '<span data-localize="yes"></span>' : '<span data-localize="no"></span>';
                            return yesNo;
                        }
                    },
                    {
                        "data": "agent_id",
                        "orderable": false,
                        render: function (data, type, row) {
                            if (row.is_master == 1) {
                                var link = '<a href="agent.php?id=' + row.agent_id + '" data-localize="edit"></a>';
                                <?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>
                                    var link = '<a href="agent.php?id=' + row.agent_id + '" data-localize="edit"></a> | <a href="#" class="deleteAgentRow" data-localize="delete"></a>';
                                <?php } else { ?>
                                    var link = '<a href="agent.php?id=' + row.agent_id + '" data-localize="edit"></a>';
                                <?php } ?>
                            } else {
                                link = '<a href="agent.php?id=' + row.agent_id + '" data-localize="edit"></a> | <a href="#" class="deleteAgentRow" data-localize="delete"></a>';
                            }
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });

        });</script>

    <?php
}
if ($basename == 'chats.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {


            $('#chats_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getchats', 'agentId': agentId}
                },
                "columns": [
                    {"data": "date_created"},
                    {"data": "room_id"},
                    {"data": "messages", "orderable": false},
                    {"data": "agent", "orderable": false}
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

    <?php
}
if ($basename == 'users.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {
            $(document).on('click', '.deleteUserRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.user_id, 'user', e);
            });

            var dataTable = $('#users_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'asc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getusers'}
                },
                "columns": [

                    {
                        "data": "name",
                        "name": "name",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "username",
                        "name": "username",
                        render: function (data, type) {
                            return data;
                        }
                    },                    {
                        "data": "is_blocked",
                        "data": "is_blocked",
                        "orderable": false,
                        render: function (data, type) {
                            var yesNo = (data == "1") ? '<span data-localize="yes"></span>' : '<span data-localize="no"></span>';
                            return yesNo;
                        }
                    },
                    {
                        "data": "user_id",
                        "orderable": false,
                        render: function (data, type) {
                            var link = '<a href="user.php?id=' + data + '" data-localize="edit"></a> | <a href="#" class="deleteUserRow" data-localize="delete"></a>';
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

    <?php
}
if ($basename == 'rooms.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {
            $(document).on('click', '.deleteClassRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.room_id, 'room', e);
            });

            var dataTable = $('#rooms_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'desc']],
                "processing": true,
                "serverSide": true,
                "createdRow": function (row, data, index) {
                    $('td', row).eq(0).attr('id', 'roomid_' + data.roomId);

                },
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getrooms', 'agentId': agentId}
                },
                "columns": [
                    {
                        "data": "roomId",
                        "name": "roomId",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "agent",
                        "name": "agent",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "visitor",
                        "data": "visitor",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "agenturl",
                        "name": "agenturl",
                        render: function (data, type, row) {
                            let link = '<a target="_blank" title="Conference agent URL" href="' + row.agenturl + '" data-localize="start"></a> | <a title="Conference agent URL" href="#" onclick="copyUrl(\'' + row.agenturl + '\', \'infoModalLabelAgent\');" data-localize="copy"></a>';
                            return link;
                        }
                    },
                    {
                        "data": "visitorurl",
                        "data": "visitorurl",
                        render: function (data, type, row) {
                            let link = '<a target="_blank" title="Conference visitor URL" href="' + row.visitorurl + '" data-localize="start"></a> | <a title="Conference visitor URL" href="#" onclick="copyUrl(\'' + row.visitorurl + '\', \'infoModalLabelVisitor\');" data-localize="copy"></a>';
                            return link;
                        }
                    },
                    {
                        "data": "datetime",
                        "data": "datetime",
                        render: function (data, type, row) {
                            var datetimest = '';
                            if (row.datetime) {
                                datetimest = getCurrentDateFormatted(row.datetime) + ' / ';
                            }
                            if (row.duration) {
                                datetimest += row.duration;
                            }
                            return datetimest;
                        }
                    },
                    {
                        "data": "is_active",
                        "data": "is_active",
                        render: function (data, type) {
                            var isActive = (data == "1") ? '<span data-localize="yes">Yes</span>' : '<span data-localize="no">No</span>';
                            return isActive;
                        }
                    },
                    {
                        "data": "room_id",
                        "orderable": false,
                        render: function (data, type) {
                            let link = '<a href="room.php?id=' + data + '" data-localize="edit"></a> | <a href="#" class="deleteClassRow" data-localize="delete"></a>';
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

    <?php
}
if ($basename == 'integration.php') {
    ?>
    <script>
        jQuery(document).ready(function ($) {

            var getCurrentDateFormatted = function (date) {
                var currentdate = new Date(date);
                if (currentdate.getDate()) {
                    return currentdate.format('isoDate')

                } else {
                    return '';
                }
            };

            var dataTable = $('#rooms_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'desc']],
                "processing": true,
                "serverSide": true,
                "createdRow": function (row, data, index) {
                    $('td', row).eq(0).attr('id', 'roomid_' + data.roomId);

                },
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getrooms', 'agentId': agentId}
                },
                "columns": [
                    {
                        "data": "roomId",
                        "name": "roomId",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "agent",
                        "name": "agent",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "visitor",
                        "data": "visitor",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "agenturl",
                        "name": "agenturl",
                        render: function (data, type, row) {
                            let link = '<a target="_blank" title="Conference agent URL" href="' + row.agenturl + '" data-localize="start"></a> | <a title="Conference agent URL" href="#" onclick="copyUrl(\'' + row.agenturl + '\', \'infoModalLabelAgent\');" data-localize="copy"></a>';
                            return link;
                        }
                    },
                    {
                        "data": "visitorurl",
                        "data": "visitorurl",
                        render: function (data, type, row) {
                            let link = '<a target="_blank" title="Conference visitor URL" href="' + row.visitorurl + '" data-localize="start"></a> | <a title="Conference visitor URL" href="#" onclick="copyUrl(\'' + row.visitorurl + '\', \'infoModalLabelVisitor\');" data-localize="copy"></a>';
                            return link;
                        }
                    },
                    {
                        "data": "is_active",
                        "data": "is_active",
                        render: function (data, type) {
                            var isActive = (data == "1") ? '<span data-localize="yes">Yes</span>' : '<span data-localize="no">No</span>';
                            return isActive;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
            <?php
            $fileConfig = $_GET['wplogin'];
            if (!file_exists('../config/'.$fileConfig.'.json')) {
                $fileConfig = 'config';
            }
            ?>

            $('#error').hide();
            let voices = [];
            window.speechSynthesis.onvoiceschanged = () => {
                voices = window.speechSynthesis.getVoices();
                let voiceSelect = document.querySelector("#text_speech_lang");
                voices.forEach((voice, i) => (voiceSelect.options[i] = new Option(`${voice.lang} (${voice.name})`, voice.lang, i)));
            };
            $('#videoScreen_exitMeetingDrop').on('change', function () {
                if (this.value == 3) {
                    $('#videoScreen_exitMeeting').show();
                } else {
                    $('#videoScreen_exitMeeting').hide();
                }
            });
            $('#saveConfig').click(function (event) {
                if ($('#videoScreen_exitMeetingDrop').val() == 1) {
                    var exitMeeting = false;
                } else if ($('#videoScreen_exitMeetingDrop').val() == 2) {
                    exitMeeting = '/';
                } else if ($('#videoScreen_exitMeetingDrop').val() == 3) {
                    exitMeeting = $('#videoScreen_exitMeeting').val()
                }
                var dataObj = {'type': 'updateconfig', 'fileName': '<?php echo $fileConfig; ?>', 'data': {
                        'agentName': $('#agentName').val(),
                        'smartVideoLanguage': $('#smartVideoLanguage').val(),
                        'recording.enabled': $('#recording_enabled').prop('checked'),
                        'recording.screen': $('#recording_screen').prop('checked'),
                        'recording.saveServer': $('#recording_saveServer').prop('checked'),
                        'recording.autoStart': $('#recording_autoStart').prop('checked'),
                        'recording.filename': $('#recording_filename').val(),
                        'recording.download': $('#recording_download').prop('checked'),
                        'whiteboard.enabled': $('#whiteboard_enabled').prop('checked'),
                        'sharevideo.enabled': $('#sharevideo_enabled').prop('checked'),
                        'videoScreen.localFeedMirrored': $('#videoScreen_localFeedMirrored').prop('checked'),
                        'videoScreen.dateFormat': $('#videoScreen_dateFormat').val(),
                        'videoScreen.exitMeetingOnTime': $('#videoScreen_exitMeetingOnTime').prop('checked'),
                        'videoScreen.exitMeetingOnTimeAgent': $('#videoScreen_exitMeetingOnTimeAgent').prop('checked'),
                        'videoScreen.meetingTimer': $('#videoScreen_meetingTimer').prop('checked'),
                        'videoScreen.disableVideoAudio': $('#videoScreen_disableVideoAudio').prop('checked'),
                        'videoScreen.disableVideo': $('#videoScreen_disableVideo').prop('checked'),
                        'videoScreen.disableAttendeeVideosOff': $('#videoScreen_disableAttendeeVideosOff').prop('checked'),
                        'videoScreen.hostOnlyAccess': $('#videoScreen_hostOnlyAccess').prop('checked'),
                        'videoScreen.admit': $('#videoScreen_admit').prop('checked'),
                        'videoScreen.lockedFeed': $('#videoScreen_lockedFeed').prop('checked'),
                        'videoScreen.getSnapshot': $('#videoScreen_getSnapshot').prop('checked'),
                        'videoScreen.getReaction': $('#videoScreen_getReaction').prop('checked'),
                        'videoScreen.breakout': $('#videoScreen_breakout').prop('checked'),
                        'videoScreen.terms': $('#videoScreen_terms').val(),
                        'videoScreen.greenRoom': $('#videoScreen_greenRoom').prop('checked'),
                        'videoScreen.videoConstraint': ($('#videoScreen_videoConstraint').val()) ? JSON.parse($('#videoScreen_videoConstraint').val()) : '{"width": {"min": 640, "ideal": 1920, "max": 3840}, "height": {"min": 480, "ideal": 1080, "max": 2160}, "aspectRatio": 1.777, "frameRate": {"min": 5, "ideal": 15, "max": 30}}',
                        'videoScreen.audioConstraint': ($('#videoScreen_audioConstraint').val()) ? JSON.parse($('#videoScreen_audioConstraint').val()) : '{"echoCancellation": true, "noiseSuppression": true, "sampleRate": 44100}',
                        'videoScreen.screenConstraint': ($('#videoScreen_screenConstraint').val()) ? JSON.parse($('#videoScreen_screenConstraint').val()) : '{"frameRate": {"min": 5, "ideal": 15, "max": 30}}',
                        'serverSide.chatHistory': $('#serverSide_chatHistory').prop('checked'),
                        'serverSide.loginForm': $('#serverSide_loginForm').prop('checked'),
                        'serverSide.checkRoom': $('#serverSide_checkRoom').prop('checked'),
                        'transcribe.enabled': $('#transcribe_enabled').prop('checked'),
                        'transcribe.voiceCommands': $('#transcribe_voiceCommands').prop('checked'),
                        'transcribe.languageTo': $('#transcribe_languageTo').val(),
                        'transcribe.language': $('#transcribe_language').val(),
                        'transcribe.apiKey': $('#transcribe_apiKey').val(),
                        'transcribe.textSpeechLang': $('#text_speech_lang').val(),
                        'transcribe.textSpeechChat': $('#text_speech_chat').prop('checked'),
                        'transcribe.textSpeechTranscribe': $('#text_speech_transcribe').prop('checked'),
                        'virtualBackground.blur': $('#virtual_blur').prop('checked'),
                        'virtualBackground.backgrounds': $('#virtual_backgrounds').prop('checked'),
                        'videoScreen.enable_chat_gpt': $('#enable_chat_gpt').prop('checked'),
                        'entryForm.visitorName_enabled': $('#visitorName_enabled').prop('checked'),
                        'serverSide.payment_enabled': $('#payment_enabled').prop('checked'),
                        'videoScreen.exitMeeting': exitMeeting,
                        'metaTitle': $('#metaTitle').val(),
                    }};
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = '/dash/integration.php?wplogin=<?php echo $_GET['wplogin'];?>&url=<?php echo $_GET['url'];?>';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_config_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });


    <?php
    $jsonString = file_get_contents('../config/' . $fileConfig . '.json');
    $data = json_decode($jsonString);
    ?>

            $('#agentName').val('<?php echo @$data->agentName; ?>');
            $('#metaTitle').val('<?php echo @$data->metaTitle; ?>');
            $('#smartVideoLanguage').val('<?php echo @$data->smartVideoLanguage; ?>');
            $('#videoScreen_localFeedMirrored').prop('checked', <?php echo @$data->videoScreen->localFeedMirrored; ?>);
            $('#recording_enabled').prop('checked', <?php echo @$data->recording->enabled; ?>);
            $('#recording_screen').prop('checked', <?php echo @$data->recording->screen; ?>);
            $('#recording_saveServer').prop('checked', <?php echo @$data->recording->saveServer; ?>);
            $('#recording_autoStart').prop('checked', <?php echo @$data->recording->autoStart; ?>);
            $('#recording_filename').val('<?php echo @$data->recording->filename; ?>');
            $('#recording_download').prop('checked', <?php echo @$data->recording->download; ?>);
            $('#whiteboard_enabled').prop('checked', <?php echo @$data->whiteboard->enabled; ?>);
            $('#sharevideo_enabled').prop('checked', <?php echo @$data->sharevideo->enabled; ?>);
            $('#videoScreen_dateFormat').val('<?php echo @$data->videoScreen->dateFormat; ?>');
            $('#videoScreen_exitMeetingOnTime').prop('checked', <?php echo @$data->videoScreen->exitMeetingOnTime; ?>);
            $('#videoScreen_exitMeetingOnTimeAgent').prop('checked', <?php echo @$data->videoScreen->exitMeetingOnTimeAgent; ?>);
            $('#videoScreen_meetingTimer').prop('checked', <?php echo @$data->videoScreen->meetingTimer; ?>);
            $('#videoScreen_disableVideoAudio').prop('checked', <?php echo @$data->videoScreen->disableVideoAudio; ?>);
            $('#videoScreen_disableVideo').prop('checked', <?php echo @$data->videoScreen->disableVideo; ?>);
            $('#videoScreen_disableAttendeeVideosOff').prop('checked', <?php echo @$data->videoScreen->disableAttendeeVideosOff; ?>);
            $('#videoScreen_hostOnlyAccess').prop('checked', <?php echo @$data->videoScreen->hostOnlyAccess; ?>);
            $('#videoScreen_admit').prop('checked', <?php echo @$data->videoScreen->admit; ?>);
            $('#videoScreen_lockedFeed').prop('checked', <?php echo @$data->videoScreen->lockedFeed; ?>);
            $('#videoScreen_getSnapshot').prop('checked', <?php echo @$data->videoScreen->getSnapshot; ?>);
            $('#videoScreen_getReaction').prop('checked', <?php echo @$data->videoScreen->getReaction; ?>);
            $('#videoScreen_breakout').prop('checked', <?php echo @$data->videoScreen->breakout; ?>);
            $('#videoScreen_terms').val('<?php echo @$data->videoScreen->terms; ?>');
            $('#videoScreen_greenRoom').prop('checked', <?php echo @$data->videoScreen->greenRoom; ?>);
            $('#serverSide_chatHistory').prop('checked', <?php echo @$data->serverSide->chatHistory; ?>);
            $('#serverSide_loginForm').prop('checked', <?php echo @$data->serverSide->loginForm; ?>);
            $('#serverSide_checkRoom').prop('checked', <?php echo @$data->serverSide->checkRoom; ?>);
            $('#transcribe_enabled').prop('checked', <?php echo @$data->transcribe->enabled; ?>);
            $('#transcribe_voiceCommands').prop('checked', <?php echo @$data->transcribe->voiceCommands; ?>);
            $('#transcribe_language').val('<?php echo @$data->transcribe->language; ?>');
            $('#transcribe_languageTo').val('<?php echo @$data->transcribe->languageTo; ?>');
            $('#transcribe_apiKey').val('<?php echo @$data->transcribe->apiKey; ?>');
            $('#text_speech_chat').prop('checked', <?php echo @$data->transcribe->textSpeechChat; ?>);
            $('#text_speech_transcribe').prop('checked', <?php echo @$data->transcribe->textSpeechTranscribe; ?>);
            $('#videoScreen_videoConstraint').val('<?php echo (isset($data->videoScreen->videoConstraint)) ? json_encode($data->videoScreen->videoConstraint, JSON_FORCE_OBJECT) : '{"width": {"min": 640, "ideal": 1920, "max": 3840}, "height": {"min": 480, "ideal": 1080, "max": 2160}, "aspectRatio": 1.777, "frameRate": {"min": 5, "ideal": 15, "max": 30}}'; ?>');
            $('#videoScreen_audioConstraint').val('<?php echo (isset($data->videoScreen->audioConstraint)) ? json_encode($data->videoScreen->audioConstraint, JSON_FORCE_OBJECT) : '{"echoCancellation": true, "noiseSuppression": true, "sampleRate": 44100}'; ?>');
            $('#videoScreen_screenConstraint').val('<?php echo (isset($data->videoScreen->screenConstraint)) ? json_encode($data->videoScreen->screenConstraint, JSON_FORCE_OBJECT) : '{"frameRate": {"min": 5, "ideal": 15, "max": 30}}'; ?>');
            $('#virtual_blur').prop('checked', <?php echo @$data->virtualBackground->blur; ?>);
            $('#virtual_backgrounds').prop('checked', <?php echo @$data->virtualBackground->backgrounds; ?>);
            $('#enable_chat_gpt').prop('checked', <?php echo @$data->videoScreen->enable_chat_gpt; ?>);
            $('#visitorName_enabled').prop('checked', <?php echo @$data->entryForm->visitorName_enabled; ?>);
            $('#payment_enabled').prop('checked', <?php echo @$data->serverSide->payment_enabled; ?>);
            var exitMeeting = '<?php echo addslashes($data->videoScreen->exitMeeting); ?>';
            if (exitMeeting == false) {
                $('#videoScreen_exitMeetingDrop').val(1);
                $('#videoScreen_exitMeeting').hide();
            } else if (exitMeeting == '/') {
                $('#videoScreen_exitMeetingDrop').val(2);
                $('#videoScreen_exitMeeting').hide();
            } else {
                $('#videoScreen_exitMeetingDrop').val(3);
                $('#videoScreen_exitMeeting').show();
                $('#videoScreen_exitMeeting').val(exitMeeting);
            }
            setTimeout(function () {
                $('#text_speech_lang').val('<?php echo @$data->transcribe->textSpeechLang; ?>');
            }, 300);
        });</script>

    <?php
}
if ($basename == 'meetings.php') {
    ?>
    <script>
        jQuery(document).ready(function ($) {
            function activeMeetings () {
                $.ajaxSetup({
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('authorization', '<?php echo $apiKey;?>');
                    }
                });
                $.ajax({
                    type: 'GET',
                    dataType: 'json',
                    url: '/api/meetings'
                })
                .done(function (data) {
                    if (data) {
                        var result = data.meetings;
                        dataTable
                            .clear()
                            .draw();
                        $('#total_meetings').html(result.length);
                        $.each(result, function (i, item) {
                            var attendees = '';
                            item.peers.forEach((peer) => {
                                var part = '';
                                for (const key in peer) {
                                    if (key == 'name') {
                                        part += '<h6>' + peer[key] + '</h6>';
                                    } else {
                                        if (peer[key]) {
                                            part += `${key} : ${peer[key]}<br/>`;
                                        }
                                    }
                                }
                                // var part = Object.entries(peer).map(([key, value]) => `${key}: ${value}`).join("<br/>");
                                part = part.replaceAll(': 1<br/>', ': <i class="fas fa-check" style="color: green;"></i><br/>');
                                part = part.replaceAll(': 0<br/>', ': <i class="fas fa-minus"></i><br/>');
                                part = part.replaceAll('true', '<i class="fas fa-check" style="color: green;"></i>');
                                part = part.replaceAll('false', '<i class="fas fa-minus"></i>');
                                attendees += '<div style="border-style: outset; height: 210px; min-height: 210px; width:200px; padding:5px; margin: 2px; float:left; max-width: 200px;">' + part + '</div>';
                            });
                            let room = {};
                            room.admin = 1;
                            const agentUrl = item.id + '?p=' + window.btoa(unescape(encodeURIComponent(JSON.stringify(room))));
                            dataTable.row.add([item.id, attendees, '<a href="/' + agentUrl + '" target="_blank"><span data-localize="join_host">Join as host</span></a> | <a href="/' + item.id + '" target="_blank"><span data-localize="join_visitor">Join as visitor</span></a>']).draw(false);
                        });
                        var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                        $('[data-localize]').localize('dashboard', opts);
                    }
                })
                .fail(function () {
                    console.log(false);
                });
            }


            setInterval(function() {
                activeMeetings();
            }, 6000);
            var dataTable = $('#rooms_table').DataTable({
                "order": [[0, 'desc']],
                "language": {
                    "url": "locales/table.json"
                },
                searching: false, paging: false, info: false
            });
            activeMeetings();
        });</script>

    <?php
}

if ($basename == 'dash.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {

            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getrooms', 'agentId': agentId}
            })
                    .done(function (data) {
                        if (data) {
                            var result = JSON.parse(data);
                            $('#roomsCount').html(result.recordsTotal);
                        }
                    })
                    .fail(function () {
                        console.log(false);
                    });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getagents', 'agentId': agentId}
            })
                    .done(function (data) {
                        if (data) {
                            var result = JSON.parse(data);
                            $('#agentsCount').html(result.recordsTotal);
                        }
                    })
                    .fail(function () {
                        console.log(false);
                    });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getusers', 'agentId': agentId}
            })
                    .done(function (data) {
                        if (data) {
                            var result = JSON.parse(data);
                            $('#usersCount').html(result.recordsTotal);
                        }
                    })
                    .fail(function () {
                        console.log(false);
                    });
            $.ajaxSetup({cache: false});
            $.getJSON('https://www.new-dev.com/versionsfu/version.json', function (data) {
                if (data) {
                    var currentVersion = '<?php echo $currentVersion; ?>';
                    var newNumber = data.version.split('.');
                    var curNumber = currentVersion.split('.');
                    var isNew = false;
                    if (parseInt(curNumber[0]) < parseInt(newNumber[0])) {
                        isNew = true;
                    }
                    if (parseInt(curNumber[0]) == parseInt(newNumber[0]) && parseInt(curNumber[1]) < parseInt(newNumber[1])) {
                        isNew = true;
                    }
                    if (parseInt(curNumber[0]) == parseInt(newNumber[0]) && parseInt(curNumber[1]) == parseInt(newNumber[1]) && parseInt(curNumber[2]) < parseInt(newNumber[2])) {
                        isNew = true;
                    }

                    if (isNew) {
    <?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>
                            $('#remoteVersion').html('<span data-localize="new_lsv_version"></span>' + data.version + '<br/><br/><span data-localize="new_lsv_features"></span><br/>' + data.text + '<br/><br/><span data-localize="update_location"></span>');
    <?php } else { ?>
                            $('#remoteVersion').html('<span data-localize="new_lsv_version"></span>' + data.version + '<br/><br/><span data-localize="new_lsv_features"></span><br/>' + data.text);
    <?php } ?>
                    } else {
                        $('#remoteVersion').html('<span data-localize="version_uptodate"></span>');
                    }

                } else {
                    $('#remoteVersion').html('<span data-localize="cannot_connect"></span>');
                }
                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true, callback: function (data, defaultCallback) {
                        document.title = data.title;
                        defaultCallback(data);
                    }};
                $('[data-localize]').localize('dashboard', opts);
            });
        });</script>

    <?php
}
if ($basename == 'user.php') {
    ?>


    <script>


        jQuery(document).ready(function ($) {
            $('#error').hide();
            $('#saveUser').click(function (event) {
                var isBlocked = ($('#is_blocked').prop('checked')) ? 1 : 0;
    <?php
    if (isset($_GET['id'])) {
        ?>
                    var name = $('#first_name').val() + ' ' + $('#last_name').val();
                    var dataObj = {'type': 'edituser', 'userId': <?php echo $_GET['id']; ?>, 'name': name, 'firstName': $('#first_name').val(), 'lastName': $('#last_name').val(), 'username': $('#email').val(), 'password': $('#password').val(), 'isBlocked': isBlocked, 'agentId': agentId};
        <?php
    } else {
        ?>
                    var dataObj = {'type': 'adduser', 'username': $('#email').val(), 'firstName': $('#first_name').val(), 'lastName': $('#last_name').val(), 'name': $('#first_name').val() + ' ' + $('#last_name').val(), 'password': $('#password').val(), 'isBlocked': isBlocked, 'agentId': agentId};
        <?php
    }
    ?>
                $.ajax({
                    type: 'POST',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'users.php';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_user_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function () {
                        });
            });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getuser', 'id': <?php echo (int) @$_GET['id'] ?>}
            })
                    .done(function (data) {
                        if (data) {
                            data = JSON.parse(data);
                            $('#userTitle').html(data.name);
                            $('#username').val(data.username);
                            if (data.password) {
                                $('#leftblank').html('<span data-localize="left_blank_changed"></span>');
                            }
                            //$('#password').val(data.password);
                            if (!data.first_name && !data.last_name) {
                                var name = data.name.split(' ');
                                data.first_name = name[0];
                                data.last_name = name[1];
                            }
                            $('#first_name').val(data.first_name);
                            $('#last_name').val(data.last_name);
                            $('#email').val(data.username);
                            $('#is_blocked').prop('checked', (data.is_blocked == "1"));
                            var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                            $('[data-localize]').localize('dashboard', opts);
                        }
                    })
                    .fail(function (e) {
                        console.log(e);
                    });
        });</script>

    <?php
}
if ($basename == 'room.php') {
    ?><script>
    <?php
    if (isset($_GET['id'])) {
        ?>

                var queryStr = function (url) {
                    var query_string = {};
                    var query = url.substring(1);
                    var vars = query.split("&");
                    for (var i = 0; i < vars.length; i++) {
                        var pair = vars[i].split("=");
                        if (typeof query_string[pair[0]] === "undefined") {
                            query_string[pair[0]] = pair[1];
                        } else if (typeof query_string[pair[0]] === "string") {
                            var arr = [query_string[pair[0]], pair[1]];
                            query_string[pair[0]] = arr;
                        } else {
                            query_string[pair[0]].push(pair[1]);
                        }
                    }
                    return query_string;
                };
                $.ajax({
                    type: 'POST',
                    url: '../server/script.php',
                    data: {'type': 'getroombyid', 'room_id': <?php echo (int) @$_GET['id'] ?>}
                })
                .done(function (data) {
                    if (data) {
                        data = JSON.parse(data);
                        var parsed = {};
                        if (data.visitorurl) {
                            const parser = document.createElement('a');
                            parser.href = data.visitorurl;
                            if (parser.search) {
                                parsed = JSON.parse(decodeURIComponent(escape(window.atob(queryStr(parser.search).p))));
                            }
                        }
                        $('#roomName').val(data.roomId);
                        $('#names').val(data.agent);
                        $('#visitorName').val(data.visitor);
                        $('#config').val(parsed.config + '.json');
                        if (data.datetime) {
                            let current_datetime = new Date(data.datetime);
                            var formatted_date = (current_datetime.getMonth() + 1) + '/' + current_datetime.getDate() + '/' + current_datetime.getFullYear() + ' ' + current_datetime.getHours() + ':' + current_datetime.getMinutes();
                            $('#datetime').val(formatted_date);
                        }

                        $('#duration').val(data.duration);
                        if (data.duration != 15 || data.duration != 30 || data.duration != 45) {
                            $('#durationtext').val(data.duration);
                        }
                        $('#active').prop('checked', (data.is_active == "1"));
                    }
                })
                .fail(function (e) {
                    console.log(e);
                });
        <?php
    }
    ?>
            jQuery(document).ready(function ($) {
                $('#error').hide();
                $('#saveRoom').on('click', function () {
                    let random = Math.random().toString(36).slice(2).substring(0, 10);
                    let roomObject = {};
                    if ($('#roomName').val()) {
                        random = $('#roomName').val();
                    }
                    if ($('#names').val()) {
                        roomObject.agentName = $('#names').val();
                    }
                    if ($('#visitorName').val()) {
                        roomObject.visitorName = $('#visitorName').val();
                    }
                    if ($('#datetime').val()) {
                        let datetime = new Date($('#datetime').val()).toISOString();
                        roomObject.datetime = datetime;
                    }
                    if ($('#config').val()) {
                        roomObject.config = $('#config').val().replace('.json', '');
                    }
                    if ($('#duration').val() || $('#durationtext').val()) {
                        let duration = ($('#durationtext').val()) ? $('#durationtext').val() : $('#duration').val();
                        roomObject.duration = duration;
                    }
                    let pass = ($('#password').val()) ? $('#password').val() : '';
                    if ($('#disableVideo').prop('checked')) {
                        roomObject.disableVideo = true;
                    }
                    if ($('#disableAll').prop('checked')) {
                        roomObject.disableAll = true;
                    }
                    generateLink(roomObject, random, false, pass);
                    var datetime = ($('#datetime').val()) ? new Date($('#datetime').val()).toISOString() : '';
                    var duration = ($('#durationtext').val()) ? $('#durationtext').val() : $('#duration').val();

    <?php
    if (isset($_GET['id'])) {
        ?>
                        var dataObj = {'room_id': '<?php echo $_GET['id']; ?>', 'type': 'editroom', 'roomId': random, 'agentId': agentId, 'agent': $('#names').val(), 'agenturl': agentUrl, 'visitor': $('#visitorName').val(), 'visitorurl': visitorUrl, 'is_active': 1, 'datetime': datetime, 'duration': duration, 'session': random, 'is_active': $('#active').prop('checked')};
        <?php
    } else {
        ?>
                        var dataObj = {'type': 'scheduling', 'roomId': random, 'agentId': agentId, 'agent': $('#names').val(), 'agenturl': agentUrl, 'visitor': $('#visitorName').val(), 'visitorurl': visitorUrl, 'is_active': 1, 'datetime': datetime, 'duration': duration, 'session': random, 'is_active': $('#active').prop('checked')};
        <?php
    }
    ?>
                    $.ajax({
                        type: 'POST',
                        url: '../server/script.php',
                        data: dataObj
                    })
                            .done(function (data) {
                                if (data == 200) {
                                    location.href = 'rooms.php';
                                } else {
                                    $('#error').show();
                                    $('#error').html('<span data-localize="error_room_save"></span>');
                                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                    $('[data-localize]').localize('dashboard', opts);
                                }
                            })
                            .fail(function () {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_room_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            });
                });
                var d = new Date();
                $('#datetime').datetimepicker({

                    format: 'MM/DD/YYYY HH:mm',
                    minDate: new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), 0),
                    icons: {
                        time: 'fa fa-clock',
                        date: 'fa fa-calendar',
                        up: 'fa fa-chevron-up',
                        down: 'fa fa-chevron-down',
                        previous: 'fa fa-chevron-left',
                        next: 'fa fa-chevron-right',
                        today: 'fa fa-check',
                        clear: 'fa fa-trash',
                        close: 'fa fa-times'
                    }
                });
            });



    </script>

<?php }
if ($basename == 'paymentoptions.php') {
?>
    <script>
        jQuery(document).ready(function ($) {
            $('#error').hide();

            $(".answer").hide();
            $('#email_notification').click(function() {
                if($(this).is(":checked")) {
                    $('#email_templates').show();
                } else {
                    $('#email_templates').hide();
                }
            });
            $('#saveOptions').click(function (event) {
                var dataObj = {'type': 'updatepaymentoption',
                        'is_enabled': $('#is_enabled').prop('checked'),
                        'paypal_client_id': $('#paypal_client_id').val(),
                        'paypal_secret_id': $('#paypal_secret_id').val(),
                        'stripe_client_id': $('#stripe_client_id').val(),
                        'stripe_secret_id': $('#stripe_secret_id').val(),
                        'authorizenet_api_login_id': $('#authorizenet_api_login_id').val(),
                        'authorizenet_transaction_key': $('#authorizenet_transaction_key').val(),
                        'authorizenet_public_client_key': $('#authorizenet_public_client_key').val(),
                        'email_notification': $('#email_notification').prop('checked'),
                        'is_test_mode': $('#is_test_mode').prop('checked'),
                        'authorizenet_enabled': $('#authorizenet_enabled').prop('checked'),
                        'paypal_enabled': $('#paypal_enabled').prop('checked'),
                        'stripe_enabled': $('#stripe_enabled').prop('checked'),
                        'email_subject': $('#email_subject').val(),
                        'email_body': $('#email_body').val(),
                        'email_from': $('#email_from').val(),
                        'email_day_notify': $('#email_day_notify').val()
                    };
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'paymentoptions.php';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_config_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });

        $.ajax({
            type: 'POST',
            url: '../server/script.php',
            data: {'type': 'getpaymentoptions'}
        })
        .done(function (data) {
            if (data) {
                data = JSON.parse(data);
                $('#paypal_client_id').val(data.paypal_client_id);
                $('#paypal_secret_id').val(data.paypal_secret_id);
                $('#stripe_client_id').val(data.stripe_client_id);
                $('#stripe_secret_id').val(data.stripe_secret_id);
                $('#authorizenet_api_login_id').val(data.authorizenet_api_login_id);
                $('#authorizenet_transaction_key').val(data.authorizenet_transaction_key);
                $('#authorizenet_public_client_key').val(data.authorizenet_public_client_key);
                $('#email_subject').val(data.email_subject);
                $('#email_body').val(data.email_body);
                $('#email_from').val(data.email_from);
                $('#email_day_notify').val(data.email_day_notify);
                $('#is_enabled').prop('checked', (data.is_enabled == '1'));
                $('#email_notification').prop('checked', (data.email_notification == '1'));
                $('#is_test_mode').prop('checked', (data.is_test_mode == '1'));
                $('#authorizenet_enabled').prop('checked', (data.authorizenet_enabled == '1'));
                $('#paypal_enabled').prop('checked', (data.paypal_enabled == '1'));
                $('#stripe_enabled').prop('checked', (data.stripe_enabled == '1'));
                if (data.email_notification == '1') {
                    $('#email_templates').show();
                } else {
                    $('#email_templates').hide();
                }
            }
        })
        .fail(function (e) {
            console.log(e);
        });
        });</script>

<?php
}
if ($basename == 'plans.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {
            $(document).on('click', '.deletePlanRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.plan_id, 'plan', e);
            });

            var dataTable = $('#plans_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'asc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getplans'}
                },
                "columns": [

                    {
                        "data": "name",
                        "name": "name",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "price",
                        "name": "price",
                        render: function (data, type, row) {
                            var link = row.price + ' ' + row.currency;
                            return link;
                        }
                    },
                    {
                        "data": "interval",
                        "name": "interval",
                        // render: function (data, type) {
                        //     return data;
                        // }
                        render: function (data, type, row) {
                            var link = row.interval_count +  ' <span data-localize="' + row.interval + '"></span>';
                            return link;
                        }
                    },
                    {
                        "data": "plan_id",
                        "orderable": false,
                        render: function (data, type) {
                            var link = '<a href="plan.php?id=' + data + '" data-localize="edit"></a> | <a href="#" class="deletePlanRow" data-localize="delete"></a>';
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

<?php
}
if ($basename == 'plan.php') {
    ?>


    <script>


        jQuery(document).ready(function ($) {
            $('#error').hide();
            $('#savePlan').click(function (event) {
    <?php
    if (isset($_GET['id'])) {
        ?>
                    var dataObj = {'type': 'editplan', 'planId': <?php echo $_GET['id']; ?>, 'name': $('#name').val(), 'price': $('#price').val(), 'currency': $('#currency').val(), 'interval': $('#interval').val(), 'interval_count': $('#interval_count').val(), 'description': $('#description').val()};
        <?php
    } else {
        ?>
                    var dataObj = {'type': 'addplan', 'name': $('#name').val(), 'price': $('#price').val(), 'currency': $('#currency').val(), 'interval': $('#interval').val(), 'interval_count': $('#interval_count').val(), 'description': $('#description').val()};
        <?php
    }
    ?>
                $.ajax({
                    type: 'POST',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'plans.php';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_plan_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getplan', 'id': <?php echo (int) @$_GET['id'] ?>}
            })
                    .done(function (data) {
                        if (data) {
                            data = JSON.parse(data);
                            $('#name').val(data.name);
                            $('#price').val(data.price);
                            $('#currency').val(data.currency);
                            $('#interval').val(data.interval);
                            $('#interval_count').val(data.interval_count);
                            $('#description').val(data.description);
                            var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                            $('[data-localize]').localize('dashboard', opts);
                        }
                    })
                    .fail(function (e) {
                        console.log(e);
                    });
        });</script>

    <?php
}
if ($basename == 'subscriptions.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {
            $(document).on('click', '.deleteSubscriptionRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.subscription_id, 'subscription', e);
            });

            var dataTable = $('#subscriptions_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getsubscriptions'}
                },
                "columns": [

                    {
                        "data": "payer_name",
                        "name": "payer_name",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "payer_email",
                        "name": "payer_email",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "valid_from",
                        "name": "valid_from",
                        // render: function (data, type) {
                        //     return data;
                        // }
                        render: function (data, type, row) {
                            var link = getCurrentDateFormatted(row.valid_from, 'mediumDate') + ' - ' + getCurrentDateFormatted(row.valid_to, 'mediumDate');
                            return link;
                        }
                    },
                    {
                        "data": "status",
                        "name": "status",
                        // render: function (data, type) {
                        //     return data;
                        // }
                        render: function (data, type, row) {
                            var link = row.payment_status;
                            if (new Date(row.valid_to).getTime() < new Date().getTime()) {
                                var bar = '<i class="fas fa-fw fa-minus" style="color:red;"></i> ';
                                link = '<span data-localize="expired"></span>'
                            } else {
                                bar = '<i class="fas fa-fw fa-check" style="color:green;"></i> ';
                            }
                            return bar + link;
                        }
                    },
                    {
                        "data": "tenant",
                        "name": "tenant",
                        render: function (data, type, row) {
                            <?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>
                                return '<a href="history.php?tenant=' + row.tenant + '">' + row.tenant + '</a>';
                            <?php } else { ?>
                                return row.tenant;
                            <?php } ?>
                        }
                    },
                    {
                        "data": "subscription_id",
                        "orderable": false,
                        render: function (data, type) {
                            var link = '<a href="subscription.php?id=' + data + '" data-localize="edit"></a> | <a href="#" class="deleteSubscriptionRow" data-localize="delete"></a>';
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

<?php
}
if ($basename == 'subscription.php') {
    ?>


    <script>


        jQuery(document).ready(function ($) {
            $('#error').hide();
            $('#saveSubscription').click(function (event) {
    <?php
    if (isset($_GET['id'])) {
        ?>
                var valid_from = ($('#valid_from').val()) ? new Date($('#valid_from').val()).toISOString().slice(0, 19).replace('T', ' ') : '';
                var valid_to = ($('#valid_to').val()) ? new Date($('#valid_to').val()).toISOString().slice(0, 19).replace('T', ' ') : '';
                var dataObj = {'type': 'editsubscription', 'subscriptionId': <?php echo $_GET['id']; ?>, 'valid_from': valid_from, 'valid_to': valid_to};
        <?php
    }
    ?>
                $.ajax({
                    type: 'POST',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'subscriptions.php';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_plan_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getsubscription', 'id': <?php echo (int) @$_GET['id'] ?>}
            })
                    .done(function (data) {
                        if (data) {
                            data = JSON.parse(data);
                            const options = {
                                year: "numeric",
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: false
                            };
                            $('#agent_name').html(data.first_name + ' ' + data.last_name);
                            $('#plan_name').html(data.name);
                            $('#payment_method').html(data.payment_method);
                            $('#payment_id').html(data.payment_id);
                            $('#txn_id').html(data.txn_id);
                            $('#paid_amount').html(data.amount + ' ' + data.currency);
                            $('#payment_status').html(data.payment_status);
                            $('#ipn_track_id').html(data.ipn_track_id);
                            $('#payer_name').html(data.payer_name);
                            $('#payer_email').html(data.payer_email);
                            let valid_from_dt = new Date(data.valid_from);
                            var formatted_valid_from = new Intl.DateTimeFormat("en-US", options).format(valid_from_dt);
                            $('#valid_from').val(formatted_valid_from);
                            let valid_to_dt = new Date(data.valid_to);
                            var formatted_valid_to = new Intl.DateTimeFormat("en-US", options).format(valid_to_dt);
                            $('#valid_to').val(formatted_valid_to);
                            $('#subscr_interval').html(data.subscr_interval);
                            $('#subscr_interval_count').html(data.subscr_interval_count);
                            var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                            $('[data-localize]').localize('dashboard', opts);
                        }
                    })
                    .fail(function (e) {
                        console.log(e);
                    });

                    $('#valid_from').datetimepicker({
                        format: 'MM/DD/YYYY, HH:mm',
                        icons: {
                            time: 'fa fa-clock',
                            date: 'fa fa-calendar',
                            up: 'fa fa-chevron-up',
                            down: 'fa fa-chevron-down',
                            previous: 'fa fa-chevron-left',
                            next: 'fa fa-chevron-right',
                            today: 'fa fa-check',
                            clear: 'fa fa-trash',
                            close: 'fa fa-times'
                        }
                    });

                    $('#valid_to').datetimepicker({
                        format: 'MM/DD/YYYY HH:mm',
                        icons: {
                            time: 'fa fa-clock',
                            date: 'fa fa-calendar',
                            up: 'fa fa-chevron-up',
                            down: 'fa fa-chevron-down',
                            previous: 'fa fa-chevron-left',
                            next: 'fa fa-chevron-right',
                            today: 'fa fa-check',
                            clear: 'fa fa-trash',
                            close: 'fa fa-times'
                        }
                    });
        });</script>

    <?php
}
if ($basename == 'subscribe.php') {
    ?>


    <script>
        jQuery(document).ready(function ($) {
            $('#stripe_payment').hide();
            $('#authorizenet_payment').hide();
            $('#manual').hide();
            $('input:radio').click(function() {
                $('#price').val($(this).attr('data-price'));
                $('#currency').val($(this).attr('data-currency'));
                $('#item_name').val($(this).attr('data-item_name'));
            });
            $('#payment_method').change(function() {
                if ($('#payment_method').val() === 'stripe') {
                    $('#stripe_payment').show();
                    $('#authorizenet_payment').hide();
                    $('#manual').hide();
                } else if ($('#payment_method').val() === 'authorizenet') {
                    $('#stripe_payment').hide();
                    $('#authorizenet_payment').show();
                    $('#manual').hide();
                } else if ($('#payment_method').val() === 'manual') {
                    $('#manual').show();
                    $('#stripe_payment').hide();
                    $('#authorizenet_payment').hide();
                } else {
                    $('#stripe_payment').hide();
                    $('#authorizenet_payment').hide();
                    $('#manual').hide();
                }
            });
        });</script>

    <?php
}
if ($basename == 'history.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {
            var dataTable = $('#history_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'gethistory'<?php if (isset($_GET['tenant']) && @$_SESSION["tenant"] == 'lsv_mastertenant') { echo ', \'tenant\': \''. $_GET['tenant'] . '\''; } ?>}
                },
                "columns": [

                    {
                        "data": "payment_id",
                        "name": "payment_id",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "payer_name",
                        "name": "payer_name",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "payer_email",
                        "name": "payer_email",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "valid_from",
                        "name": "valid_from",
                        // render: function (data, type) {
                        //     return data;
                        // }
                        render: function (data, type, row) {
                            var link = getCurrentDateFormatted(row.valid_from, 'longDate') + ' - ' + getCurrentDateFormatted(row.valid_to, 'longDate');
                            return link;
                        }
                    },
                    {
                        "data": "payment_status",
                        "name": "payment_status",
                        render: function (data, type, row) {
                            var link = row.payment_status;
                            if (new Date(row.valid_to).getTime() < new Date().getTime()) {
                                var bar = '<i class="fas fa-fw fa-minus" style="color:red;"></i> ';
                                link = '<span data-localize="expired"></span>'
                            } else {
                                bar = '<i class="fas fa-fw fa-check" style="color:green;"></i> ';
                            }
                            return bar + link;
                        }
                    },
                    {
                        "data": "name",
                        "name": "name",
                        // render: function (data, type) {
                        //     return data;
                        // }
                        render: function (data, type, row) {
                            <?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>
                                var link = '<a href="plan.php?id=' + row.plan_id + '">' + row.name + '</a>';
                            <?php } else { ?>
                                var link = row.name;
                            <?php } ?>
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

<?php
}
?>
<script>
    jQuery(document).ready(function ($) {
        var opts = {language: 'en', pathPrefix: 'locales', loadBase: true, callback: function (data, defaultCallback) {
                document.title = data.title;
                defaultCallback(data);
            }};
        $('[data-localize]').localize('dashboard', opts);
    });
    fetch('./locales/dashboard.json')
        .then((response) => response.json())
        .then((json) => {
            $.each( json, function (i, val) {
                $('#' + i).attr('title', val);
            });
        });
</script>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/bootstrap-datetimepicker.js"></script>
<script src="js/jquery.localize.js" type="text/javascript" charset="utf-8"></script>
</body>

</html>