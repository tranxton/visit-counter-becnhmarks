// import necessary modules
import {check} from 'k6';
import http from 'k6/http';

// define configuration
export let options = {
	thresholds: {
		http_req_failed: ['rate<=0.00'],
		http_req_duration: ['p(95)<8', 'p(99)<15'],
		http_reqs: ['count >= 624000']//RPS >= 15600
	},
	stages: [
		{duration: '10s', target: 100},
		{duration: '20s', target: 100},
		{duration: '10s', target: 0}
	]
};

export default function () {
	const res = http.get('http://php:8080/visits');

	check(res, {'response code is 200': (res) => res.status === 200});
}