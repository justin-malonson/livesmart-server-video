<?php
include_once 'header.php';
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left" data-localize="rooms"></h6>
        <div class="float-right"><a href="javascript:void(0);" id="generateLink"><h6 class="m-0 font-weight-bold text-primary" data-localize="start_video"></h6></a></div>
    </div>
    <div class="card-body">
        <div class="table-responsive">

            <table class="table table-bordered" id="rooms_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                    <th class="text-center" data-localize="room"></th>
                        <th class="text-center" data-localize="agent"></th>
                        <th class="text-center" data-localize="visitor"></th>
                        <th class="text-center" data-localize="agent_url"></th>
                        <th class="text-center" data-localize="visitor_url"></th>
                        <th class="text-center" data-localize="active"></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>

            </table>
        </div>
    </div>
</div>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left" data-localize="configurations"></h6>
    </div>
    <div class="card-body">
        <div class="col-sm-6">
            <div class="p-1">
                <h6 data-localize="config_info"></h6>
                <br/>
                <form class="user">
                    <div class="form-group">
                        <h6 data-localize="config_entryForm"></h6>
                        <label for="agentName" data-localize="config_agent_name"></label>
                        <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_agent_name_info"></i>
                        <input type="text" class="form-control" id="agentName" aria-describedby="agentName">
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" value="1" id="visitorName_enabled">
                        <label class="custom-control-label" for="visitorName_enabled" data-localize="visitorName_enabled"></label>
                        <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="visitorName_enabled_info"></i>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_greenRoom">
                        <label class="custom-control-label" for="videoScreen_greenRoom" data-localize="config_greenroom"></label>
                        <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_greenroom_info"></i>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="names"><h6 data-localize="config_language"></h6></label>
                        <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_language_info"></i>
                        <select class="form-control" name="smartVideoLanguage" id="smartVideoLanguage">
                            <?php
                            if ($handle = opendir('../locales')) {

                                while (false !== ($entry = readdir($handle))) {

                                    if ($entry[0] != "." && $entry != "." && $entry != ".." && substr($entry, -3) != "zip") {
                                        $entry = substr($entry, 0, -5);
                                        echo '<option value="' . $entry . '">' . $entry . '</option>';
                                    }
                                }

                                closedir($handle);
                            }
                            ?>
                        </select>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h6 data-localize="config_recordings"></h6>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="recording_enabled">
                            <label class="custom-control-label" for="recording_enabled" data-localize="config_enabled"></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="recording_screen">
                            <label class="custom-control-label" for="recording_screen" data-localize="config_screen"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_screen_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="recording_saveServer">
                            <label class="custom-control-label" for="recording_saveServer" data-localize="config_saveserver"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_saveserver_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="recording_autoStart">
                            <label class="custom-control-label" for="recording_autoStart" data-localize="config_autostart"></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="recording_download">
                            <label class="custom-control-label" for="recording_download" data-localize="config_download"></label>
                        </div>
                        <hr>
                        <div class="custom-control">
                            <label for="recording_filename" data-localize="config_filename"></label>
                            <input type="text" class="form-control" id="recording_filename" aria-describedby="recording_filename">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h6 data-localize="config_whiteboard"></h6>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="whiteboard_enabled">
                            <label class="custom-control-label" for="whiteboard_enabled" data-localize="config_enabled"></label>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h6 data-localize="config_sharevideo"></h6>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="sharevideo_enabled">
                            <label class="custom-control-label" for="sharevideo_enabled" data-localize="config_enabled"></label>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h6 data-localize="config_videopanel"></h6>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_localFeedMirrored">
                            <label class="custom-control-label" for="videoScreen_localFeedMirrored" data-localize="config_localfeedmirrored"></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_exitMeetingOnTime">
                            <label class="custom-control-label" for="videoScreen_exitMeetingOnTime" data-localize="config_exitmeetingontime"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_exitmeetingontime_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_meetingTimer">
                            <label class="custom-control-label" for="videoScreen_meetingTimer" data-localize="config_meetingtimer"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_meetingtimer_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_exitMeetingOnTimeAgent">
                            <label class="custom-control-label" for="videoScreen_exitMeetingOnTimeAgent" data-localize="config_exitmeetingontimeagent"></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_disableVideoAudio">
                            <label class="custom-control-label" for="videoScreen_disableVideoAudio" data-localize="diable_audio_video"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="diable_audio_video_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_disableVideo">
                            <label class="custom-control-label" for="videoScreen_disableVideo" data-localize="diable_video"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="diable_video_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_disableAttendeeVideosOff">
                            <label class="custom-control-label" for="videoScreen_disableAttendeeVideosOff" data-localize="diable_attendee_video_off"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="diable_attendee_video_off_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_hostOnlyAccess">
                            <label class="custom-control-label" for="videoScreen_hostOnlyAccess" data-localize="host_only_action"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="host_only_action_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_admit">
                            <label class="custom-control-label" for="videoScreen_admit" data-localize="config_admit"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_admit_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_lockedFeed">
                            <label class="custom-control-label" for="videoScreen_lockedFeed" data-localize="config_small_local_feed"></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_getSnapshot">
                            <label class="custom-control-label" for="videoScreen_getSnapshot" data-localize="config_snapshot"></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="enable_chat_gpt">
                            <label class="custom-control-label" for="enable_chat_gpt" data-localize="enable_chat_gpt"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="enable_chat_gpt_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_getReaction">
                            <label class="custom-control-label" for="videoScreen_getReaction" data-localize="config_reaction"></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="videoScreen_breakout">
                            <label class="custom-control-label" for="videoScreen_breakout" data-localize="enable_breakout"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="enable_breakout_info"></i>
                        </div>
                        <div class="custom-control">
                            <label for="videoScreen_dateFormat" data-localize="config_dateformat"></label>
                            <select class="form-control" name="videoScreen_dateFormat" id="videoScreen_dateFormat">
                                <option value="default"><?php echo date('d-m-Y G:i'); ?></option>
                                <option value="isoDate"><?php echo date('Y-m-d G:i'); ?></option>
                                <option value="shortDate"><?php echo date('m/d/y G:i'); ?></option>
                                <option value="longDate"><?php echo date('F j, Y G:i'); ?></option>
                                <option value="fullDate"><?php echo date('D, F j, Y G:i'); ?></option>
                            </select>
                        </div>
                        <div class="custom-control">
                            <label for="videoScreen_terms" data-localize="config_terms"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_terms_info"></i>
                            <input type="text" class="form-control" id="videoScreen_terms" aria-describedby="terms">
                        </div>
                        <hr>
                        <div class="custom-control">
                            <label for="videoScreen_videoConstraint" data-localize="config_videoconstraint"></label>
                            <textarea class="form-control" id="videoScreen_videoConstraint"></textarea>
                        </div>
                        <hr>
                        <div class="custom-control">
                            <label for="videoScreen_audioConstraint" data-localize="config_audioconstraint"></label>
                            <textarea class="form-control" id="videoScreen_audioConstraint"></textarea>
                        </div>
                        <hr>
                        <div class="custom-control">
                            <label for="videoScreen_screenConstraint" data-localize="config_screenshareconstraint"></label>
                            <textarea class="form-control" id="videoScreen_screenConstraint"></textarea>
                        </div>
                        <hr>
                        <div class="custom-control">
                            <label for="videoScreen_exitMeetingDrop" data-localize="config_exitMeeting"></label>
                            <select class="form-control" name="videoScreen_exitMeetingDrop" id="videoScreen_exitMeetingDrop"><option value="1">Show entry form</option><option value="2">Go to home page</option><option value="3">Go to specific URL</option></select>
                            <input type="text" class="form-control" id="videoScreen_exitMeeting" aria-describedby="videoScreen_exitMeeting">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h6 data-localize="config_speechtranslate"></h6>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="transcribe_enabled">
                            <label class="custom-control-label" for="transcribe_enabled" data-localize="config_enabled"></label>
                        </div>
                        <hr>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="transcribe_voiceCommands">
                            <label class="custom-control-label" for="transcribe_voiceCommands" data-localize="config_voiceCommands"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_voiceCommands_info"></i>
                        </div>
                        <hr>
                        <div class="custom-control">
                            <label for="transcribe_language" data-localize="config_language_from"></label>
                            <!-- <select class="form-control" name="transcribe_language" id="transcribe_language"></select> -->
                            <select class="form-control" name="transcribe_language" id="transcribe_language"><option value="af">Afrikaans (af)</option><option value="sq">Albanian (sq)</option><option value="am">Amharic (am)</option><option value="ar">Arabic (ar)</option><option value="hy">Armenian (hy)</option><option value="az">Azerbaijani (az)</option><option value="eu">Basque (eu)</option><option value="be">Belarusian (be)</option><option value="bn">Bengali (bn)</option><option value="bs">Bosnian (bs)</option><option value="bg">Bulgarian (bg)</option><option value="ca">Catalan (ca)</option><option value="ceb">Cebuano (ceb)</option><option value="ny">Chichewa (ny)</option><option value="zh">Chinese (Simplified) (zh)</option><option value="zh-TW">Chinese (Traditional) (zh-TW)</option><option value="co">Corsican (co)</option><option value="hr">Croatian (hr)</option><option value="cs">Czech (cs)</option><option value="da">Danish (da)</option><option value="nl">Dutch (nl)</option><option value="en" selected="selected">English (en)</option><option value="eo">Esperanto (eo)</option><option value="et">Estonian (et)</option><option value="tl">Filipino (tl)</option><option value="fi">Finnish (fi)</option><option value="fr">French (fr)</option><option value="fy">Frisian (fy)</option><option value="gl">Galician (gl)</option><option value="ka">Georgian (ka)</option><option value="de">German (de)</option><option value="el">Greek (el)</option><option value="gu">Gujarati (gu)</option><option value="ht">Haitian Creole (ht)</option><option value="ha">Hausa (ha)</option><option value="haw">Hawaiian (haw)</option><option value="iw">Hebrew (iw)</option><option value="hi">Hindi (hi)</option><option value="hmn">Hmong (hmn)</option><option value="hu">Hungarian (hu)</option><option value="is">Icelandic (is)</option><option value="ig">Igbo (ig)</option><option value="id">Indonesian (id)</option><option value="ga">Irish (ga)</option><option value="it">Italian (it)</option><option value="ja">Japanese (ja)</option><option value="jw">Javanese (jw)</option><option value="kn">Kannada (kn)</option><option value="kk">Kazakh (kk)</option><option value="km">Khmer (km)</option><option value="ko">Korean (ko)</option><option value="ku">Kurdish (Kurmanji) (ku)</option><option value="ky">Kyrgyz (ky)</option><option value="lo">Lao (lo)</option><option value="la">Latin (la)</option><option value="lv">Latvian (lv)</option><option value="lt">Lithuanian (lt)</option><option value="lb">Luxembourgish (lb)</option><option value="mk">Macedonian (mk)</option><option value="mg">Malagasy (mg)</option><option value="ms">Malay (ms)</option><option value="ml">Malayalam (ml)</option><option value="mt">Maltese (mt)</option><option value="mi">Maori (mi)</option><option value="mr">Marathi (mr)</option><option value="mn">Mongolian (mn)</option><option value="my">Myanmar (Burmese) (my)</option><option value="ne">Nepali (ne)</option><option value="no">Norwegian (no)</option><option value="ps">Pashto (ps)</option><option value="fa">Persian (fa)</option><option value="pl">Polish (pl)</option><option value="pt">Portuguese (pt)</option><option value="pa">Punjabi (pa)</option><option value="ro">Romanian (ro)</option><option value="ru">Russian (ru)</option><option value="sm">Samoan (sm)</option><option value="gd">Scots Gaelic (gd)</option><option value="sr">Serbian (sr)</option><option value="st">Sesotho (st)</option><option value="sn">Shona (sn)</option><option value="sd">Sindhi (sd)</option><option value="si">Sinhala (si)</option><option value="sk">Slovak (sk)</option><option value="sl">Slovenian (sl)</option><option value="so">Somali (so)</option><option value="es">Spanish (es)</option><option value="su">Sundanese (su)</option><option value="sw">Swahili (sw)</option><option value="sv">Swedish (sv)</option><option value="tg">Tajik (tg)</option><option value="ta">Tamil (ta)</option><option value="te">Telugu (te)</option><option value="th">Thai (th)</option><option value="tr">Turkish (tr)</option><option value="uk">Ukrainian (uk)</option><option value="ur">Urdu (ur)</option><option value="uz">Uzbek (uz)</option><option value="vi">Vietnamese (vi)</option><option value="cy">Welsh (cy)</option><option value="xh">Xhosa (xh)</option><option value="yi">Yiddish (yi)</option><option value="yo">Yoruba (yo)</option><option value="zu">Zulu (zu)</option></select>
                        </div>
                        <hr>
                        <div class="custom-control">
                            <label for="transcribe_languageTo" data-localize="config_secondlanguage"></label>
                            <!-- <select class="form-control" name="transcribe_languageTo" id="transcribe_languageTo"></select> -->
                            <select class="form-control" name="transcribe_languageTo" id="transcribe_languageTo"><option value="af">Afrikaans (af)</option><option value="sq">Albanian (sq)</option><option value="am">Amharic (am)</option><option value="ar">Arabic (ar)</option><option value="hy">Armenian (hy)</option><option value="az">Azerbaijani (az)</option><option value="eu">Basque (eu)</option><option value="be">Belarusian (be)</option><option value="bn">Bengali (bn)</option><option value="bs">Bosnian (bs)</option><option value="bg">Bulgarian (bg)</option><option value="ca">Catalan (ca)</option><option value="ceb">Cebuano (ceb)</option><option value="ny">Chichewa (ny)</option><option value="zh">Chinese (Simplified) (zh)</option><option value="zh-TW">Chinese (Traditional) (zh-TW)</option><option value="co">Corsican (co)</option><option value="hr">Croatian (hr)</option><option value="cs">Czech (cs)</option><option value="da">Danish (da)</option><option value="nl">Dutch (nl)</option><option value="en" selected="selected">English (en)</option><option value="eo">Esperanto (eo)</option><option value="et">Estonian (et)</option><option value="tl">Filipino (tl)</option><option value="fi">Finnish (fi)</option><option value="fr">French (fr)</option><option value="fy">Frisian (fy)</option><option value="gl">Galician (gl)</option><option value="ka">Georgian (ka)</option><option value="de">German (de)</option><option value="el">Greek (el)</option><option value="gu">Gujarati (gu)</option><option value="ht">Haitian Creole (ht)</option><option value="ha">Hausa (ha)</option><option value="haw">Hawaiian (haw)</option><option value="iw">Hebrew (iw)</option><option value="hi">Hindi (hi)</option><option value="hmn">Hmong (hmn)</option><option value="hu">Hungarian (hu)</option><option value="is">Icelandic (is)</option><option value="ig">Igbo (ig)</option><option value="id">Indonesian (id)</option><option value="ga">Irish (ga)</option><option value="it">Italian (it)</option><option value="ja">Japanese (ja)</option><option value="jw">Javanese (jw)</option><option value="kn">Kannada (kn)</option><option value="kk">Kazakh (kk)</option><option value="km">Khmer (km)</option><option value="ko">Korean (ko)</option><option value="ku">Kurdish (Kurmanji) (ku)</option><option value="ky">Kyrgyz (ky)</option><option value="lo">Lao (lo)</option><option value="la">Latin (la)</option><option value="lv">Latvian (lv)</option><option value="lt">Lithuanian (lt)</option><option value="lb">Luxembourgish (lb)</option><option value="mk">Macedonian (mk)</option><option value="mg">Malagasy (mg)</option><option value="ms">Malay (ms)</option><option value="ml">Malayalam (ml)</option><option value="mt">Maltese (mt)</option><option value="mi">Maori (mi)</option><option value="mr">Marathi (mr)</option><option value="mn">Mongolian (mn)</option><option value="my">Myanmar (Burmese) (my)</option><option value="ne">Nepali (ne)</option><option value="no">Norwegian (no)</option><option value="ps">Pashto (ps)</option><option value="fa">Persian (fa)</option><option value="pl">Polish (pl)</option><option value="pt">Portuguese (pt)</option><option value="pa">Punjabi (pa)</option><option value="ro">Romanian (ro)</option><option value="ru">Russian (ru)</option><option value="sm">Samoan (sm)</option><option value="gd">Scots Gaelic (gd)</option><option value="sr">Serbian (sr)</option><option value="st">Sesotho (st)</option><option value="sn">Shona (sn)</option><option value="sd">Sindhi (sd)</option><option value="si">Sinhala (si)</option><option value="sk">Slovak (sk)</option><option value="sl">Slovenian (sl)</option><option value="so">Somali (so)</option><option value="es">Spanish (es)</option><option value="su">Sundanese (su)</option><option value="sw">Swahili (sw)</option><option value="sv">Swedish (sv)</option><option value="tg">Tajik (tg)</option><option value="ta">Tamil (ta)</option><option value="te">Telugu (te)</option><option value="th">Thai (th)</option><option value="tr">Turkish (tr)</option><option value="uk">Ukrainian (uk)</option><option value="ur">Urdu (ur)</option><option value="uz">Uzbek (uz)</option><option value="vi">Vietnamese (vi)</option><option value="cy">Welsh (cy)</option><option value="xh">Xhosa (xh)</option><option value="yi">Yiddish (yi)</option><option value="yo">Yoruba (yo)</option><option value="zu">Zulu (zu)</option></select>
                        </div>
                        <hr>
                        <div class="custom-control">
                            <label for="transcribe_apiKey" data-localize="config_apikey"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_apikey_info"></i>
                            <input type="text" class="form-control" id="transcribe_apiKey" aria-describedby="transcribe_apiKey">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h6 data-localize="config_text_speech"></h6>
                        <div class="custom-control">
                            <label for="text_speech_lang" data-localize="config_text_speech_lang"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_text_speech_lang_info"></i>
                            <select class="form-control" name="text_speech_lang" id="text_speech_lang"></select>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="text_speech_chat">
                            <label class="custom-control-label" for="text_speech_chat" data-localize="config_text_speech_chat"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_text_speech_chat_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="text_speech_transcribe">
                            <label class="custom-control-label" for="text_speech_transcribe" data-localize="config_text_speech_transcribe"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_text_speech_transcribe_info"></i>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h6 data-localize="config_virtual_backgrounds"></h6>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="virtual_blur">
                            <label class="custom-control-label" for="virtual_blur" data-localize="virtual_blur"></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="virtual_backgrounds">
                            <label class="custom-control-label" for="virtual_backgrounds" data-localize="virtual_backgrounds"></label>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <h6 data-localize="config_serverside"></h6>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="serverSide_chatHistory">
                            <label class="custom-control-label" for="serverSide_chatHistory" data-localize="config_chathistory"></label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="serverSide_loginForm">
                            <label class="custom-control-label" for="serverSide_loginForm" data-localize="config_loginform"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_loginform_info"></i>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" value="1" id="serverSide_checkRoom">
                            <label class="custom-control-label" for="serverSide_checkRoom" data-localize="config_roomaccess"></label>
                            <i class="fas fa-info-circle" data-bs-toggle="tooltip" id="config_roomaccess_info"></i>
                        </div>
                    </div>
                    <hr>
                    <a href="javascript:void(0);" id="saveConfig" class="btn btn-primary btn-user btn-block">
                        Save
                    </a>
                    <hr>


                </form>

            </div>

        </div>
    </div>
</div>
<?php
include_once 'footer.php';
?>
