'use strict';

module.exports = class Peer {
    constructor(socket_id, data) {
        this.id = socket_id;
        this.peer_info = data.peer_info;
        this.peer_name = data.peer_info.peer_name;
        this.peer_presenter = data.peer_info.peer_presenter;
        this.peer_audio = data.peer_info.peer_audio;
        this.peer_video = data.peer_info.peer_video;
        this.peer_video_privacy = data.peer_video_privacy;
        this.peer_hand = data.peer_info.peer_hand;
        this.transports = new Map();
        this.consumers = new Map();
        this.producers = new Map();
    }

    // ####################################################
    // UPDATE PEER INFO
    // ####################################################

    setPeer(data) {
        switch (data.type) {
            case 'audio':
            case 'audioType':
                this.peer_info.peer_audio = data.status;
                this.peer_audio = data.status;
                break;
            case 'video':
            case 'videoType':
                this.peer_info.peer_video = data.status;
                this.peer_video = data.status;
                if (data.status == false) {
                    this.peer_info.peer_video_privacy = data.status;
                    this.peer_video_privacy = data.status;
                }
                break;
            case 'screen':
            case 'screenType':
                this.peer_info.peer_screen = data.status;
                break;
            case 'hand':
                this.peer_info.peer_hand = data.status;
                this.peer_hand = data.status;
                break;
            case 'privacy':
                this.peer_info.peer_video_privacy = data.status;
                this.peer_video_privacy = data.status;
                break;
            case 'presenter':
                this.peer_info.peer_presenter = data.status;
                this.peer_presenter = data.status;
                break;
            case 'waiting':
                this.peer_info.peer_waiting = data.status;
                this.peer_waiting = data.status;
                break;
            default:
                break;
        }
    }

    // ####################################################
    // TRANSPORT
    // ####################################################

    addTransport(transport) {
        this.transports.set(transport.id, transport);
    }

    async connectTransport(transport_id, dtlsParameters) {
        if (!this.transports.has(transport_id)) return;

        await this.transports.get(transport_id).connect({
            dtlsParameters: dtlsParameters,
        });
    }

    close() {
        this.transports.forEach((transport) => transport.close());
    }

    // ####################################################
    // PRODUCER
    // ####################################################

    getProducer(producer_id) {
        return this.producers.get(producer_id);
    }

    async createProducer(producerTransportId, rtpParameters, kind, type) {
        let producer = await this.transports.get(producerTransportId).produce({
            kind,
            rtpParameters,
        });

        producer.appData.mediaType = type;

        this.producers.set(producer.id, producer);


        if (['simulcast', 'svc'].includes(producer.type)) {
        }

        producer.on(
            'transportclose',
            function () {
                producer.close();
                this.producers.delete(producer.id);
            }.bind(this),
        );

        return producer;
    }

    closeProducer(producer_id) {
        if (!this.producers.has(producer_id)) return;
        try {
            this.producers.get(producer_id).close();
        } catch (ex) {
            //cosole.log(ex)
        }
        this.producers.delete(producer_id);
    }

    // ####################################################
    // CONSUMER
    // ####################################################

    async createConsumer(consumer_transport_id, producer_id, rtpCapabilities) {
        let consumerTransport = this.transports.get(consumer_transport_id);
        let consumer = null;

        try {
            consumer = await consumerTransport.consume({
                producerId: producer_id,
                rtpCapabilities,
                paused: false,
            });
        } catch (error) {
            return console.error('Consume failed', error);
        }

        // https://www.w3.org/TR/webrtc-svc/

        switch (consumer.type) {
            case 'simulcast':
                // L1T3/L2T3/L3T3
                await consumer.setPreferredLayers({
                    spatialLayer: 3, // 1/2/3
                    temporalLayer: 3,
                });
                break;
            case 'svc':
                // L3T3
                await consumer.setPreferredLayers({
                    spatialLayer: 3,
                    temporalLayer: 3,
                });
                break;
            default:
                break;
        }

        this.consumers.set(consumer.id, consumer);

        consumer.on(
            'transportclose',
            function () {
                this.removeConsumer(consumer.id);
            }.bind(this),
        );

        return {
            consumer,
            params: {
                producerId: producer_id,
                id: consumer.id,
                kind: consumer.kind,
                rtpParameters: consumer.rtpParameters,
                type: consumer.type,
                producerPaused: consumer.producerPaused,
            },
        };
    }

    removeConsumer(consumer_id) {
        if (this.consumers.has(consumer_id)) {
            this.consumers.delete(consumer_id);
        }
    }
};
