package game

// HIT tells if the cell is hit
const HIT string = "HIT"

// MISS tells if the hit missed
const MISS string = "MISS"

// Shot represents a shoot
type Shot struct {
	Cell  Cell
	State string
}
