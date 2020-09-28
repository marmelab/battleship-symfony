package game

import (
	"battleship/game"
	"testing"

	"github.com/corbym/gocrest/is"
	"github.com/corbym/gocrest/then"
)

func TestAddShip(t *testing.T) {
	grid := game.NewGrid(3)

	grid = game.AddShip(grid, game.Ship{1, []game.Cell{}})

	then.AssertThat(t, len(grid.Ships), is.EqualTo(1).Reason("Added one ship"))
}
