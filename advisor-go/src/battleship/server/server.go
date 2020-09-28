package server

import (
	"battleship/game"
	"battleship/scoreboard"
	"encoding/json"
	"net/http"
	"strconv"
)

const port int = 8383

// Start the API server
func Start() {
	http.HandleFunc("/", bestMove)
	http.HandleFunc("/next-shots", nextShots)
	http.ListenAndServe(":"+strconv.Itoa(port), nil)
}

func bestMove(w http.ResponseWriter, r *http.Request) {
	// board := getBoardFromRequest(r)
	grid := getGridFromRequest(r)
	// grid := game.NewGrid(10)
	// ship := game.Ship{2, []game.Cell{}}
	bestMove := scoreboard.GetScoreBoard(grid, game.Ship{2, []game.Cell{}})
	sendResponse(w, bestMove)
}

func nextShots(w http.ResponseWriter, r *http.Request) {
	// sendResponse(w, []game.Cell{{5, 6}, {5, 7}})
	grid := getGridFromRequest(r)
	nextShots := game.GetNextBestShots(grid)
	sendResponse(w, nextShots)
}

func getGridFromRequest(r *http.Request) game.Grid {
	decoder := json.NewDecoder(r.Body)
	defer r.Body.Close()
	var grid game.Grid
	err := decoder.Decode(&grid)
	if err != nil {
		panic(err)
	}

	return grid
}

func sendResponse(w http.ResponseWriter, response interface{}) {
	encodedResponse, err := json.Marshal(response)
	if err != nil {
		panic(err)
	}
	w.Header().Set("content-type", "application/json")
	w.Write([]byte(string(encodedResponse)))
}
