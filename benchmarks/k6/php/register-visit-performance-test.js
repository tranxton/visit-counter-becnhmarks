// import necessary modules
import {check} from 'k6';
import http from 'k6/http';

// define configuration
export let options = {
	thresholds: {
		http_req_failed: ['rate<=0.00'],
		http_req_duration: ['p(95)<9', 'p(99)<18'],
		http_reqs: ['count >= 540000']//RPS >= 13500
	},
	stages: [
		{duration: '10s', target: 100},
		{duration: '20s', target: 100},
		{duration: '10s', target: 0}
	]
};

export default function () {
	const url = 'http://php:8080/visits/RU';
	const payload = JSON.stringify({});
	const params = {
		headers: {
			'Content-Type': 'application/json'
		}
	};

	const res = http.post(url, payload, params);

	check(res, {'response code is 200': (res) => res.status === 200});
}