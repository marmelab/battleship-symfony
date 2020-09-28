package game

import (
	"battleship/game"
	"testing"

	"github.com/corbym/gocrest/is"
	"github.com/corbym/gocrest/then"
)

func TestGetNextBestShots(t *testing.T) {
	grid := game.NewGrid(3)
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 1}, game.HIT})

	expected := []game.Cell{
		{0, 1},
		{1, 0},
		{1, 2},
		{2, 1},
	}

	actual := game.GetNextBestShots(grid)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("Top, right, bottom and left should be proposed"))
}

func TestGetNextBestShotsWhenNoHit(t *testing.T) {
	grid := game.NewGrid(3)
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 1}, "FAIL"})

	expected := []game.Cell{}

	actual := game.GetNextBestShots(grid)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("There is not hit"))
}

func TestGetNextBestShotsOnBorders(t *testing.T) {
	grid := game.NewGrid(1)

	expected := []game.Cell{}

	actual := game.GetNextBestShots(grid)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("Should not propose outside of the grid"))
}

func TestGetNextBestShotsWithObstacleAbove(t *testing.T) {
	grid := game.NewGrid(3)
	grid = game.AddShot(grid, game.Shot{game.Cell{0, 1}, "FAIL"})
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 1}, game.HIT})

	expected := []game.Cell{
		{1, 0},
		{1, 2},
		{2, 1},
	}

	actual := game.GetNextBestShots(grid)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("The top has already been shot"))
}

func TestGetNextBestShotsWithObstacleBelow(t *testing.T) {
	grid := game.NewGrid(3)
	grid = game.AddShot(grid, game.Shot{game.Cell{2, 1}, "FAIL"})
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 1}, game.HIT})

	expected := []game.Cell{
		{0, 1},
		{1, 0},
		{1, 2},
	}

	actual := game.GetNextBestShots(grid)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("The bottom has already been shot"))
}

func TestGetNextBestShotsWithMultipleHits(t *testing.T) {
	grid := game.NewGrid(4)
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 2}, game.HIT})
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 1}, game.HIT})

	expected := []game.Cell{
		{1, 0},
		{1, 3},
	}

	actual := game.GetNextBestShots(grid)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("Only left and right should be proposed"))
}

func TestGetNextBestShotsWith3HorizontalHits(t *testing.T) {
	grid := game.NewGrid(5)
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 1}, game.HIT})
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 2}, game.HIT})
	grid = game.AddShot(grid, game.Shot{game.Cell{1, 3}, game.HIT})

	expected := []game.Cell{
		{1, 0},
		{1, 4},
	}

	actual := game.GetNextBestShots(grid)

	then.AssertThat(t, arraysContainSameElements(expected, actual), is.EqualTo(true).Reason("It has proposed more than left and right cells"))
}

func arraysContainSameElements(array1 []game.Cell, array2 []game.Cell) bool {
	if len(array1) != len(array2) {
		return false
	}

	for _, elem1 := range array1 {
		if !game.IsElementInList(elem1, array2) {
			return false
		}
	}

	return true
}

func TestArraysContainSameElements(t *testing.T) {
	array1 := []game.Cell{
		{1, 0},
		{1, 4},
	}

	array2 := []game.Cell{
		{1, 4},
		{1, 0},
	}

	then.AssertThat(t, arraysContainSameElements(array1, array2), is.EqualTo(true).Reason("The arrays does not contain same elements"))

}

func TestIsElementInList(t *testing.T) {
	list := []game.Cell{{1, 1}, {1, 2}}

	actual := game.IsElementInList(game.Cell{1, 1}, list)

	then.AssertThat(t, actual, is.EqualTo(true).Reason("Cell 1,1 is not already in list"))
}

func TestIsNotElementInList(t *testing.T) {
	list := []game.Cell{{1, 1}, {1, 2}}

	actual := game.IsElementInList(game.Cell{1, 3}, list)

	then.AssertThat(t, actual, is.EqualTo(false).Reason("Cell 1,3 is already in list"))
}

func TestHuntLeftCell(t *testing.T) {
	grid := game.NewGrid(3)

	cell := game.Cell{1, 1}

	grid = game.AddShot(grid, game.Shot{cell, game.HIT})

	nextShots := []game.Cell{}

	actual := game.HuntLeftCell(cell, grid, nextShots)

	then.AssertThat(t, actual, is.EqualTo([]game.Cell{{1, 0}}).Reason("Cell  is already in list"))
}

func TestHuntLeftCellWithMiss(t *testing.T) {
	grid := game.NewGrid(3)

	cell := game.Cell{1, 2}

	hit := game.Shot{cell, game.HIT}
	miss := game.Shot{game.Cell{1, 1}, game.MISS}

	grid = game.AddShot(grid, hit)
	grid = game.AddShot(grid, miss)

	nextShots := []game.Cell{}

	actual := game.HuntLeftCell(cell, grid, nextShots)

	then.AssertThat(t, actual, is.EqualTo([]game.Cell{}).Reason("Cell 0,1 should not be proposed"))
}

func TestHuntLeftCellWithTwoHits(t *testing.T) {
	grid := game.NewGrid(4)

	cell := game.Cell{1, 2}
	cell2 := game.Cell{1, 1}

	grid = game.AddShot(grid, game.Shot{cell, game.HIT})
	grid = game.AddShot(grid, game.Shot{cell2, game.HIT})

	nextShots := []game.Cell{}

	actual := game.HuntLeftCell(cell2, grid, nextShots)

	then.AssertThat(t, actual, is.EqualTo([]game.Cell{{1, 0}}).Reason("Cell 1,3 is already in list"))
}

func TestHuntRightCellWithTwoHits(t *testing.T) {
	grid := game.NewGrid(4)

	cell := game.Cell{1, 2}
	cell2 := game.Cell{1, 1}

	grid = game.AddShot(grid, game.Shot{cell, game.HIT})
	grid = game.AddShot(grid, game.Shot{cell2, game.HIT})

	nextShots := []game.Cell{}

	actual := game.HuntRightCell(cell, grid, nextShots)

	then.AssertThat(t, actual, is.EqualTo([]game.Cell{{1, 3}}).Reason("Cell 1,3 is already in list"))
}

func TestHuntTopCellWithTwoHits(t *testing.T) {
	grid := game.NewGrid(3)

	cell := game.Cell{2, 1}
	cell2 := game.Cell{1, 1}

	grid = game.AddShot(grid, game.Shot{cell, game.HIT})
	grid = game.AddShot(grid, game.Shot{cell2, game.HIT})

	nextShots := []game.Cell{}

	actual := game.HuntTopCell(cell, grid, nextShots)

	then.AssertThat(t, actual, is.EqualTo([]game.Cell{{0, 1}}).Reason("Cell 1,3 is already in list"))
}

func TestHuntBottomCellWithTwoHits(t *testing.T) {
	grid := game.NewGrid(3)

	cell := game.Cell{0, 1}
	cell2 := game.Cell{1, 1}

	grid = game.AddShot(grid, game.Shot{cell, game.HIT})
	grid = game.AddShot(grid, game.Shot{cell2, game.HIT})

	nextShots := []game.Cell{}

	actual := game.HuntBottomCell(cell, grid, nextShots)

	then.AssertThat(t, actual, is.EqualTo([]game.Cell{{2, 1}}).Reason("Cell 1,3 is already in list"))
}

func TestGetNextBestShotsWithAMiss(t *testing.T) {
	grid := game.NewGrid(4)

	hit := game.Shot{game.Cell{1, 1}, game.HIT}
	miss := game.Shot{game.Cell{1, 2}, game.MISS}

	grid = game.AddShot(grid, hit)
	grid = game.AddShot(grid, miss)

	expected := []game.Cell{
		{0, 1},
		{1, 0},
		{2, 1},
	}

	actual := game.GetNextBestShots(grid)

	then.AssertThat(t, actual, is.EqualTo(expected).Reason("Wrong cells return"))
	// then.AssertThat(t, arraysContainSameElements(expected, actual), is.EqualTo(true).Reason("Wrong cells return"))
}
