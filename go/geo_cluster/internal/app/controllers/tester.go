package controllers

import (
	"encoding/json"
	"fmt"
	"github.com/oschwald/geoip2-golang"
	"github.com/oschwald/maxminddb-golang"
	"log"
	"net"
	"net/http"
	"net/url"
)

type Country struct {
	EnglishName string  `json:"english_name"`
	RussianName string  `json:"russian_name"`
	IsoCode     string  `json:"iso_code"`
	TimeZone    string  `json:"time_zone"`
	Latitude    float64 `json:"latitude"`
	Longtitude  float64 `json:"longitude"`
	Stopped     bool    `json:"stopped"`
}
type Network struct {
	Network string `json:"network"`
	Mask    string `json:"mask"`
	Ip      string `json:"ip"`
	Size    int    `json:"size"`
	Stopped bool   `json:"stopped"`
}

func TesterCountry(w http.ResponseWriter, r *http.Request) {

	response := make(chan Country, 10)
	go func() {

		params, _ := url.ParseQuery(r.URL.RawQuery)
		ipParam := params.Get("ip")

		var country Country
		country.Stopped = false
		db, err := geoip2.Open("data/GeoIP2-City.mmdb")
		if err != nil {
			log.Fatal(err)
		}
		defer db.Close()

		ip := net.ParseIP(ipParam)
		if ip == nil {
			fmt.Printf("NO IP")
			fmt.Printf("%v", ip)
			country.Stopped = true
			response <- country
			return
		}

		if ip.To4() == nil {
			if db.Metadata().IPVersion == 4 {
				fmt.Printf("Wrong ip version ")
				country.Stopped = true
				response <- country
				return
			}

		}
		record, err := db.City(ip)

		if err != nil {
			log.Fatal(err)
		}

		if len(record.Subdivisions) > 0 {
			fmt.Printf("English subdivision name: %v\n", record.Subdivisions[0].Names["en"])
			country.EnglishName = record.Subdivisions[0].Names["en"]
		}
		country.RussianName = record.Country.Names["ru"]
		country.IsoCode = record.Country.IsoCode
		country.TimeZone = record.Location.TimeZone
		country.Latitude = record.Location.Latitude
		country.Longtitude = record.Location.Longitude
		response <- country
		fmt.Printf("Russian country name: %v\n", record.Country.Names["ru"])
		fmt.Printf("ISO country code: %v\n", record.Country.IsoCode)
		fmt.Printf("Time zone: %v\n", record.Location.TimeZone)
		fmt.Printf("Coordinates: %v, %v\n", record.Location.Latitude, record.Location.Longitude)
	}()

	w.Header().Set("Content-Type", "application/json")
	data := <-response
	if data.Stopped == true {
		w.WriteHeader(http.StatusBadRequest)
		w.Write([]byte("400 - Bad request error"))
		json.NewEncoder(w).Encode(map[string]interface{}{
			"result": "fail",
		})
	} else {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"result": "ok",
			"data":   data,
		})
	}

}
func getNetworks() map[string]Network {

	db, err := maxminddb.Open("data/GeoIP2-Connection-Type-Test.mmdb")
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	record := struct {
		Domain string `maxminddb:"connection_type"`
	}{}

	_, network, err := net.ParseCIDR("1.0.0.0/8")
	if err != nil {
		log.Panic(err)
	}
	mapResult := map[string]Network{}

	networks := db.NetworksWithin(network, maxminddb.SkipAliasedNetworks)

	for networks.Next() {
		subnet, err := networks.Network(&record)
		if err != nil {
			log.Panic(err)
		}
		fmt.Printf("%s: %s\n", subnet.String(), record.Domain)
		ones, _ := subnet.Mask.Size()
		mapResult[subnet.String()] = Network{
			Network: subnet.String(),
			Mask:    subnet.Mask.String(),
			Ip:      "1.0.0.0",
			Size:    ones,
		}

	}

	if networks.Err() != nil {
		log.Panic(networks.Err())
	}
	return mapResult

}
func getNetworbyIp(ip net.IP) *net.IPNet {
	db, err := maxminddb.Open("data/GeoIP2-Connection-Type-Test.mmdb")
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	var record struct {
		IPNet struct {
			IP   net.IP     // network number
			Mask net.IPMask // network mask
		}
	}
	nt, ok, err := db.LookupNetwork(ip, &record)
	if err != nil {
		log.Panic(err)
	}

	fmt.Printf("%v", ok)
	return nt
}
func TesterNetworks(w http.ResponseWriter, r *http.Request) {
	networks := getNetworks()
	fmt.Println(fmt.Sprintf("%#v", networks))

	w.Header().Set("Content-Type", "application/json")

	fmt.Printf("%v", "DATA")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"result": "ok",
		"data":   networks,
	})

}

func TesterNetwork(w http.ResponseWriter, r *http.Request) {

	response := make(chan Network, 10)
	go func() {
		var message Network
		params, _ := url.ParseQuery(r.URL.RawQuery)
		ipParam := params.Get("ip")
		ip := net.ParseIP(ipParam)
		if ip == nil {
			fmt.Printf("NO IP")
			fmt.Printf("%v", ip)
			message.Stopped = true
			response <- message
			return
		}
		fmt.Printf("%v\n", ip)

		network := getNetworbyIp(ip)

		message.Network = network.String()
		message.Mask = network.Mask.String()
		ones, _ := network.Mask.Size()
		message.Ip = ipParam
		message.Size = ones
		
		response <- message

	}()

	data := <-response
	fmt.Printf("%v", "DATA")
	fmt.Printf("%v", data)
	if data.Stopped == true {
		w.WriteHeader(http.StatusBadRequest)
		w.Write([]byte("400 - Bad request error"))
		json.NewEncoder(w).Encode(map[string]interface{}{
			"result": "fail",
		})
	} else {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"result": "ok",
			"data":   data,
		})
	}
}
