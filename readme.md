### Local START
````
//запускать из основной папки
docker compose up -d
docker compose exec api composer i
docker compose exec api php ./bin/console db.migrations.migrate
````

### Local Tests
````
docker compose exec api ./vendor/phpunit/phpunit/phpunit ./tests/
````

### RPS Tests
````
docker run --rm -i --ulimit nofile=100000:100000 --network=host grafana/k6 run - <<'K6'
import http from "k6/http";

export const options = {
  discardResponseBodies: true,
  scenarios: {
    rps: {
      executor: "constant-arrival-rate",
      rate: 8000,
      timeUnit: "1s",
      duration: "4s",
      preAllocatedVUs: 300,
      maxVUs: 5000,
    },
  },
  thresholds: { dropped_iterations: ["count==0"],
    http_req_failed: ["rate<0.001"],
  },
};

export default function () {
  const res = http.get("http://127.0.0.1:8082/api/welcome");
}
K6
````

