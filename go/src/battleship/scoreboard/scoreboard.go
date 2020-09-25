package scoreboard

import (
	"battleship/game"
	"strconv"
)

// ScoreBoard holds the probabilities of finding ships on the cells
type ScoreBoard struct {
	Length int
	Cells  [][]int
}

// NewScoreBoard creates a square board of the given size
func NewScoreBoard(size int) ScoreBoard {
	cells := make([][]int, size)

	for i := 0; i < size; i++ {
		cells[i] = make([]int, size)
	}

	return ScoreBoard{size, cells}
}

// ToString returns returns the stringify score board
func ToString(scoreBoard *ScoreBoard) string {
	res := ""
	for row := 0; row < len(scoreBoard.Cells); row++ {
		for column := 0; column < len(scoreBoard.Cells); column++ {
			res += strconv.Itoa(scoreBoard.Cells[row][column])

			if column < len(scoreBoard.Cells)-1 {
				res += " "
			} else if row < len(scoreBoard.Cells)-1 {
				res += "\n"
			}
		}
	}

	return res
}

// GetScoreBoard returns the score board of a ship positioned on a grid of the given size
func GetScoreBoard(grid game.Grid, ship game.Ship) *ScoreBoard {
	scoreBoard := NewScoreBoard(grid.Size)

	if ship.Length > grid.Size {
		return &scoreBoard
	}

	for row := 0; row < grid.Size; row++ {
		for column := 0; column < grid.Size; column++ {

			shipCanBePlacedHorizontally := shipCanBePlacedHorizontally(game.Cell{row, column}, ship, grid)
			if shipCanBePlacedHorizontally {
				for l := 0; l < ship.Length; l++ {
					scoreBoard.Cells[row][column+l]++
				}
			}

			shipCanBePlacedVertically := shipCanBePlacedVertically(game.Cell{row, column}, ship, grid)
			if shipCanBePlacedVertically {
				for l := 0; l < ship.Length; l++ {
					scoreBoard.Cells[row+l][column]++
				}
			}
		}
	}

	return &scoreBoard
}

func shipCanBePlacedHorizontally(cell game.Cell, ship game.Ship, grid game.Grid) bool {
	if cell.Column+ship.Length > grid.Size {
		return false
	}

	for _, shot := range grid.Shots {
		for i := 0; i < ship.Length; i++ {
			cellToCheck := game.Cell{cell.Row, cell.Column + i}
			if cellToCheck == shot.Cell {
				return false
			}
		}
	}

	return true
}

func shipCanBePlacedVertically(cell game.Cell, ship game.Ship, grid game.Grid) bool {
	if cell.Row+ship.Length > grid.Size {
		return false
	}

	for _, shot := range grid.Shots {
		for i := 0; i < ship.Length; i++ {
			cellToCheck := game.Cell{cell.Row + i, cell.Column}
			if cellToCheck == shot.Cell {
				return false
			}
		}
	}

	return true
}
