define([
    'jquery',
    'Amasty_CronScheduler/vendor/vis/vis-min',
    'Magento_Ui/js/modal/modal'
], function ($, vis) {
    'use strict';

    $.widget('amasty.cronTimeline', {
        options: {
            data: null,
            storeUtcOffset: null,
            jobTimeStats: ['created_at', 'scheduled_at', 'executed_at', 'finished_at']
        },

        _create: function() {
            var self = this,
                cronJobCodes = [],
                cronJobCodesFlags = [],
                cronData = this.options.data,
                countOfCronRecords = cronData.length,
                cronTimelineGroups = new vis.DataSet(),
                cronTimelineRecords = new vis.DataSet(),
                cronTimelineOptions = {
                    width: '100%',
                    height: '800px',
                    orientation: 'top',
                    stack: false,
                    verticalScroll: true,
                    zoomKey: 'ctrlKey',
                    tooltip: {
                        followMouse: true,
                        overflowMethod: 'cap'
                    },
                    start: new Date((new Date()).valueOf() - 1000*60*60*3),
                    end: new Date((new Date()).valueOf() + 1000*60*60*1),
                    moment: function (date) {
                        return vis.moment(date).utcOffset(self.options.storeUtcOffset);
                    }
                };

            for (var index = 0; index < countOfCronRecords; index++) {
                if (cronJobCodesFlags[cronData[index].job_code]) continue;
                cronJobCodesFlags[cronData[index].job_code] = true;
                cronJobCodes.push(cronData[index].job_code);
            }

            for (var index = 0; index < cronJobCodes.length; index++) {
                cronTimelineGroups.add({
                    id: cronJobCodes[index],
                    content: cronJobCodes[index]
                });
            }

            cronTimelineRecords.add(this.generateRecordsData(cronData));

            var timeline = new vis.Timeline(document.getElementById('cron-timeline'),
                cronTimelineRecords, cronTimelineGroups, cronTimelineOptions);

            $('[data-amcronsch-js="timeline-zoom-in"]').on('click', function () {
                timeline.zoomIn(0.4);
            });

            $('[data-amcronsch-js="timeline-zoom-out"]').on('click', function () {
                timeline.zoomOut(0.4);
            });
        },

        getDateValue: function (record, statuses) {
            if (statuses.indexOf(record.status) != -1) {
                return (record.executed_at !== null)
                    ? new Date(record.executed_at.replace(' ', 'T') + 'Z')
                    : '';
            } else {
                return (record.scheduled_at !== null)
                    ? new Date(record.scheduled_at.replace(' ', 'T') + 'Z')
                    : '';
            }
        },

        generateRecordsData: function (cronRecords) {
            var self = this,
                recordsForTimeline = cronRecords.map(function (record) {
                return {
                    'start': self.getDateValue(record, ['error', 'success', 'run']),
                    'end': self.getDateValue(record, ['error', 'success']),
                    'className': 'amcronsch-record -' + record.status,
                    'group': record.job_code,
                    'title': self.renderRecordInfo(record),
                    'type': 'range'
                };
            });

            return recordsForTimeline;
        },

        renderRecordInfo: function (cronRecord) {
            self = this;
            var recordInfoContainer = '',
                recordInfoContent = '';

            $.each(cronRecord, function(key, value) {
                if (self.options.jobTimeStats.includes(key) && value) {
                    value = vis.moment(
                        vis.moment(value).utcOffset(self.options.storeUtcOffset)._d
                    ).format('YYYY-MM-DD HH:mm:ss');
                }

                recordInfoContent +=
                    '<tr class="amcronsch-' + key +'">' +
                    '    <td class="amcronsch-cell amcronsch-label">' + key + '</td>' +
                    '    <td class="amcronsch-cell" ><span class="amcronsch-value' + ((key == 'status') ? (' -' + value) : '') + '">' + value + '</span></td>' +
                    '</tr>'
            });

            recordInfoContainer =
                '<div class="amcronsch-record-info">' +
                '   <table>' +
                '   <thead><th class="amcronsch-cell amcronsch-header" colspan="2"><span class="amcronsch-type">' + cronRecord['job_code'] + '</span></th></thead>' +
                recordInfoContent +
                '   </table>' +
                '   </div>';

            return recordInfoContainer;
        }
    });

    return $.amasty.cronTimeline;
});
