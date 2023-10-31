package main

import (
	"encoding/json"
	"fmt"
	"github.com/gorilla/mux"
	"github.com/joho/godotenv"
	"gitlab.com/Pereselkov777/checkurl_cluster/githooks"
	"gitlab.com/Pereselkov777/checkurl_cluster/internal/app/controllers"
	"log"
	"net/http"
	"os"
	"strings"
)

func handlerIndex(w http.ResponseWriter, r *http.Request) {
	fmt.Fprintf(w, "Hello, %s!", r.URL.Path[1:])
}

var workerAccessToken string

func JwtVerify(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		var header = r.Header.Get("worker-access-token")

		json.NewEncoder(w).Encode(r)
		header = strings.TrimSpace(header)

		if header == "" {
			w.WriteHeader(http.StatusForbidden)
			json.NewEncoder(w).Encode("Missing auth token")
			return
		} else {
			//json.NewEncoder(w).Encode(fmt.Sprintf("Token found. Value %s", header))
			if header != workerAccessToken {
				w.WriteHeader(http.StatusForbidden)
				json.NewEncoder(w).Encode("Missing auth token")
				return
			}
		}
		next.ServeHTTP(w, r)
	})
}

func main() {
	if err := godotenv.Load("../.env"); err != nil {
		log.Print("No 1 .env file found")
	}

	workerAccessToken = os.Getenv("WORKER_ACCESS_TOKEN")

	log.Println("Starting the HTTP server on port 8085")
	router := mux.NewRouter().StrictSlash(true)
	secure := router.PathPrefix("/api").Subrouter()
	secure.Use(JwtVerify)

	router.HandleFunc("/gitlab", githooks.HandlerGitlab).Methods("POST")

	secure.HandleFunc("/tester/country", controllers.TesterCountry).Methods("GET")
	secure.HandleFunc("/tester/network", controllers.TesterNetwork).Methods("GET")
	secure.HandleFunc("/tester/networks", controllers.TesterNetworks).Methods("GET")
	router.HandleFunc("/", handlerIndex).Methods("GET")

	log.Fatal(http.ListenAndServe(":8085", router))
}
