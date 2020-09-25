package game

import (
	"battleship/game"
	"battleship/scoreboard"
	"fmt"
	"strconv"
	"testing"

	"github.com/corbym/gocrest/is"
	"github.com/corbym/gocrest/then"
)

func TestGetScoreBoardWithOneCellLongShip(t *testing.T) {
	// Given one 1 cell long ship
	// on a 3x3 grid
	ship := game.Ship{1, []game.Cell{}}
	grid := game.NewGrid(3)

	cells := [][]int{
		{2, 2, 2},
		{2, 2, 2},
		{2, 2, 2},
	}

	expected := &scoreboard.ScoreBoard{3, cells}

	// When computing its possible positions
	actual := scoreboard.GetScoreBoard(grid, ship) // adresse récupérée

	// Then it should equals this score board
	then.AssertThat(t, actual, is.EqualTo(expected).Reason("1 cell long ship on 3x3 grid"))
	displayScoreBoard(actual, ship, grid)
}

func TestGetScoreBoardWithTwoCellsLongShip(t *testing.T) {
	// Given one 2 cells long ship
	ship := game.Ship{2, []game.Cell{}}
	grid := game.NewGrid(3)

	cells := [][]int{
		{2, 3, 2},
		{3, 4, 3},
		{2, 3, 2},
	}

	expected := &scoreboard.ScoreBoard{3, cells}

	// When computing its possible positions
	// on a 3x3 grid
	actual := scoreboard.GetScoreBoard(grid, ship)

	// Then it should equals this score board
	then.AssertThat(t, actual, is.EqualTo(expected).Reason("2 cells long ship on 3x3 grid"))
	displayScoreBoard(actual, ship, grid)
}

func TestGetScoreBoardWithTooLongShip(t *testing.T) {
	// Given one 4 cells long ship
	// on a 3x3 grid
	ship := game.Ship{4, []game.Cell{}}
	grid := game.NewGrid(3)

	cells := [][]int{
		{0, 0, 0},
		{0, 0, 0},
		{0, 0, 0},
	}

	expected := &scoreboard.ScoreBoard{3, cells}

	// When computing its possible positions
	actual := scoreboard.GetScoreBoard(grid, ship)

	// Then it should not be computed
	then.AssertThat(t, actual, is.EqualTo(expected).Reason("Too long ship on 3x3 grid"))
	displayScoreBoard(actual, ship, grid)
}

func TestGetScoreBoardWithObstacle(t *testing.T) {
	// Given a grid with an obstacle
	// and considering a 2 cells long ship
	grid := game.NewGrid(3)
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 2}, game.HIT})

	ship := game.Ship{2, []game.Cell{}}

	cells := [][]int{
		{2, 3, 1},
		{3, 3, 0},
		{2, 3, 1},
	}

	expected := &scoreboard.ScoreBoard{3, cells}

	// When computing possible positions of the ship
	actual := scoreboard.GetScoreBoard(grid, ship)

	// Then the resulting score board should equals the expected one
	then.AssertThat(t, actual, is.EqualTo(expected).Reason("There is an obstacle on cell 1:2"))
	displayScoreBoard(actual, ship, grid)
}

func TestGetScoreBoardWithBiggerGridWithoutObstacle(t *testing.T) {
	grid := game.NewGrid(10)

	computedShip := game.Ship{2, []game.Cell{}}

	cells := [][]int{
		{2, 3, 3, 3, 3, 3, 3, 3, 3, 2},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{2, 3, 3, 3, 3, 3, 3, 3, 3, 2},
	}

	expected := &scoreboard.ScoreBoard{10, cells}

	actual := scoreboard.GetScoreBoard(grid, computedShip)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("Bigger grid without obstacle"))
	displayScoreBoard(actual, computedShip, grid)
}

func TestGetScoreBoardWithBiggerGridAndOneObstacle(t *testing.T) {
	grid := game.NewGrid(10)
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 2}, game.HIT})

	computedShip := game.Ship{2, []game.Cell{}}

	cells := [][]int{
		{2, 3, 2, 3, 3, 3, 3, 3, 3, 2},
		{3, 3, 0, 3, 4, 4, 4, 4, 4, 3},
		{3, 4, 3, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{2, 3, 3, 3, 3, 3, 3, 3, 3, 2},
	}

	expected := &scoreboard.ScoreBoard{10, cells}

	actual := scoreboard.GetScoreBoard(grid, computedShip)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("Bigger grid with one obstacle"))
	displayScoreBoard(actual, computedShip, grid)
}

func TestGetScoreBoardWithBiggerGridAndMultipleObstacles(t *testing.T) {
	grid := game.NewGrid(10)
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 2}, game.HIT})
	grid = game.AddShot(grid, game.Shot{game.Cell{5, 6}, game.HIT})
	grid = game.AddShot(grid, game.Shot{game.Cell{8, 4}, game.HIT})

	computedShip := game.Ship{2, []game.Cell{}}

	cells := [][]int{
		{2, 3, 2, 3, 3, 3, 3, 3, 3, 2},
		{3, 3, 0, 3, 4, 4, 4, 4, 4, 3},
		{3, 4, 3, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 4, 4, 4, 3},
		{3, 4, 4, 4, 4, 4, 3, 4, 4, 3},
		{3, 4, 4, 4, 4, 3, 0, 3, 4, 3},
		{3, 4, 4, 4, 4, 4, 3, 4, 4, 3},
		{3, 4, 4, 4, 3, 4, 4, 4, 4, 3},
		{3, 4, 4, 3, 0, 3, 4, 4, 4, 3},
		{2, 3, 3, 3, 2, 3, 3, 3, 3, 2},
	}

	expected := &scoreboard.ScoreBoard{10, cells}

	actual := scoreboard.GetScoreBoard(grid, computedShip)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("Bigger grid with multiple obstacles"))
	displayScoreBoard(actual, computedShip, grid)
}

func displayScoreBoard(scoreBoard *scoreboard.ScoreBoard, ship game.Ship, grid game.Grid) {
	message := scoreboard.ToString(scoreBoard)
	message += "  "
	message += strconv.Itoa(ship.Length) + " long ship on " + strconv.Itoa(grid.Size) + "x" + strconv.Itoa(grid.Size) + " grid"
	obstaclesCount := len(grid.Ships)
	if obstaclesCount > 0 {
		message += " with " + strconv.Itoa(obstaclesCount) + " obstacle"
	}
	fmt.Println(message)
}
